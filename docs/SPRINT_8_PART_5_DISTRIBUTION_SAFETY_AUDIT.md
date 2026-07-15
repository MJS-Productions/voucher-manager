# Voucher Manager — Sprint 8 Part 5 Distribution Safety Audit

**Baseline:** Sprint 8 Part 4 green Keeper-tested source  
**Scope:** Manual distribution safety before Free 1.0  
**Code changes:** None  
**Version changes:** None

## Executive decision

Manual Distribution is strong at the database claim boundary but is **not yet fully production-safe**.

The atomic voucher claim itself is correctly protected against concurrent requests. However, two post-claim operations can currently fail after the voucher has already changed from `available` to `assigned`:

1. operational Activity persistence;
2. WordPress transient persistence for the one-time result.

The first gap can turn a successful claim into an apparent failed request. The second can make a successfully assigned voucher impossible for the administrator to recover from the UI.

A focused Distribution Hardening implementation is required before Sprint 8 can close.

## Audited path

`DistributionAdmin::distribute()`

→ capability check  
→ nonce check  
→ normalize `pool_id`  
→ `ErrorBoundary::execute()`  
→ `DistributionService::distribute()`  
→ active Pool validation  
→ atomic `claim_next_available()`  
→ remaining inventory count  
→ operational Activity write  
→ `DistributionResult`  
→ one-minute user transient  
→ POST/Redirect/GET  
→ transient consumed and deleted during render

## Findings

### 1. Capability and CSRF boundary — PASS

Distribution execution requires:

- `manage_options`
- POST through `admin-post.php`
- `check_admin_referer( 'voucher_manager_distribute_code' )`

No distribution action is executed from GET.

Decision: no change required.

### 2. Pool validation — PASS

The service re-reads the Pool by submitted ID and rejects:

- unknown Pools;
- inactive Pools.

This is important because the HTML select is only presentation. A manipulated `pool_id` does not bypass the domain check.

Decision: no change required.

### 3. Concurrent voucher claim — PASS

`WpdbCodeRepository::claim_next_available()`:

1. starts a transaction;
2. selects the oldest available row for the Pool;
3. uses `FOR UPDATE`;
4. updates only when the row is still `available`;
5. requires exactly one affected row;
6. commits only after the state transition;
7. rolls back on failed transition or exception.

The repository also asserts the domain transition:

`available → assigned`

Concurrent requests therefore cannot successfully claim the same database row.

Decision: retain this architecture.

### 4. Double-click / duplicate POST — SAFE FOR UNIQUENESS, UX RISK

Two valid POST requests can each claim a different voucher.

This is not a duplicate-code bug. Atomic claiming protects uniqueness.

However, a fast double-click or browser resubmission can intentionally or accidentally distribute two distinct vouchers.

Current behavior has no request-level idempotency token beyond the WordPress nonce. A WordPress nonce is a CSRF control, not a one-use distribution token.

Decision: add a one-use distribution intent token.

The token should:

- be generated for the rendered form;
- be scoped to the current administrator;
- be consumed atomically or effectively once before claim execution;
- reject a repeated POST with a safe message;
- never contain a voucher value;
- have a short expiry.

This protects double-click and browser resubmit without weakening the database claim boundary.

### 5. Refresh after redirect — PASS

Distribution uses POST/Redirect/GET.

The redirected GET reads the current user's transient and deletes it immediately.

Refreshing the result page does not perform another distribution.

Decision: no change required.

### 6. Back button / form resubmit — PARTIAL

A normal page refresh is safe.

A browser that restores and resubmits the prior POST can execute another valid distribution while the WordPress nonce remains valid.

This is the same request-idempotency gap identified above.

Decision: one-use distribution intent token required.

### 7. Last available voucher — PASS

The final available row can be claimed normally.

`count_available()` then returns zero and the presentation gives explicit depleted-inventory guidance.

A later request receives the clean empty result.

Decision: no change required.

### 8. Pool changes during distribution — ACCEPTABLE

The service validates Pool activity immediately before the claim.

There is still a narrow theoretical race where a Pool can be deactivated after service validation and before the code-row transaction.

The claim transaction locks the Code row, not the Pool row.

For the current manual administrator-only Free workflow, this does not create duplicate claims or voucher corruption. It can produce one final successful distribution concurrent with Pool deactivation.

Decision: document as accepted behavior for Free 1.0. Do not widen the transaction to Pool lifecycle locking without a demonstrated requirement.

### 9. Activity logging after claim — CRITICAL HARDENING GAP

Current order:

`claim code and COMMIT`

→ `count remaining`

→ `logs->add( distribution.completed )`

If `logs->add()` throws:

- the voucher is already assigned;
- `DistributionService::distribute()` throws;
- `ErrorBoundary` returns the generic failure fallback;
- the administrator is told Distribution failed;
- the code value is not returned;
- retrying can assign another voucher.

This is the most important audit finding.

Operational Activity must not be able to convert a successful business operation into an apparent failure after commit.

Decision: completed-distribution logging must be failure-contained.

Recommended rule:

- voucher claim is the authoritative business operation;
- Activity is operational visibility;
- if completed-event persistence fails, return the successful voucher result;
- contain the log failure without exposing the voucher;
- write only bounded failure metadata to PHP error logging.

Do **not** roll the voucher back to `available` after the code value has been read and claimed. A rollback/release path would create a dangerous ambiguity about whether the value was already observed.

### 10. Remaining-count failure after claim — CRITICAL HARDENING GAP

Current order also calls `count_available()` after the committed claim.

If this query throws or otherwise fails through a future stricter repository implementation:

- the voucher remains assigned;
- the service can fail before returning the code.

The remaining count is presentation metadata, not part of claim correctness.

Decision: post-claim inventory counting must not suppress the successful voucher result.

Recommended result model:

- successful claim always returns the code;
- `remaining` becomes nullable or has an explicit `remaining_known` state;
- presentation shows normal remaining guidance when known;
- presentation shows a bounded fallback such as `Remaining inventory could not be refreshed.` when unknown.

Do not report `0` when the count is unknown because zero falsely means the Pool is empty.

### 11. One-time transient persistence — CRITICAL RECOVERY GAP

After a successful service result, `DistributionAdmin` calls `set_transient()` and ignores its return value.

If transient persistence fails:

- the voucher is assigned;
- the service returned the code;
- the redirect still occurs;
- the result page has no transient;
- the administrator never sees the voucher value.

The Inventory privacy boundary intentionally does not reveal full assigned codes later. Therefore this is a real voucher-recovery loss.

Decision: transient storage cannot be the sole post-claim delivery boundary without checking persistence.

Recommended architecture:

- introduce a dedicated `DistributionResultStore`;
- store the one-time result under an opaque per-result token;
- verify storage success before redirect;
- redirect with only the opaque token, never the voucher;
- consume-and-delete the result once;
- if storage fails after claim, do not issue a normal success redirect.

Because the claim has already happened, a storage failure still needs a safe emergency presentation path in the same POST response or another guaranteed server-side result store.

Preferred Free implementation:

1. claim voucher;
2. create opaque random result token;
3. persist one-time result in a dedicated WordPress option/transient abstraction;
4. verify persistence;
5. redirect with opaque token;
6. consume and delete on GET.

If WordPress transient persistence fails, render a minimal protected one-time result directly from the POST handler instead of redirecting. This keeps the voucher visible to the authenticated administrator without placing it in the URL or Activity.

### 12. Current transient key can overwrite another result — HARDENING GAP

Current key:

`voucher_manager_distribution_{user_id}`

Two successful requests by the same administrator can overwrite the same transient before either redirected page consumes it.

This can happen with:

- multiple tabs;
- near-simultaneous POST requests;
- double submit.

The first voucher remains assigned but its one-time result can be replaced by the second voucher.

Decision: use a unique opaque result token per distribution rather than one transient slot per user.

The stored result must also include the current user ID and consumption must verify ownership.

### 13. Voucher privacy in URL — PASS

The voucher code is not added to the redirect URL.

Only page and notice state are included.

Decision: preserve this boundary.

### 14. Voucher privacy in Activity — PASS

Completed Activity context contains:

- `pool_id`
- `code_id`
- `remaining`

It does not contain the raw voucher value.

Decision: preserve this boundary.

### 15. Voucher privacy in generic failure context — PASS WITH CAUTION

`ErrorBoundary` receives:

- action;
- pool ID;
- source.

The raw voucher is not supplied as error context.

Decision: preserve this boundary and ensure new result-storage failures also never log the voucher.

## Required hardening before Free 1.0

### A. One-use Distribution Intent

Protect the request from duplicate execution caused by double-click or POST resubmission.

Suggested components:

- `Domain/Distribution/DistributionIntent`
- `Domain/Distribution/DistributionIntentStore`
- `Infrastructure/WordPress/WpDistributionIntentStore`

The form receives an opaque token.

The POST consumes the token before calling `DistributionService::distribute()`.

A repeated token returns a safe failure and performs no claim.

### B. Post-claim success must remain success

Refactor `DistributionService` so that after `claim_next_available()` returns a voucher:

- remaining-count failure is contained;
- completed Activity failure is contained;
- the voucher result is still returned successfully.

The raw code must never be included in failure logs.

### C. Unique one-time Result Store

Replace the single per-user transient slot.

Suggested components:

- `Domain/Distribution/DistributionResultStore`
- `Infrastructure/WordPress/WpDistributionResultStore`

Required properties:

- cryptographically opaque result token;
- unique per successful request;
- current-user ownership;
- short TTL;
- consume-once semantics;
- voucher never appears in URL;
- multiple tabs cannot overwrite each other.

### D. Result-store failure fallback

A successful claim must not become invisible because server-side result persistence failed.

If one-time result persistence fails:

- contain and log only bounded metadata;
- render the successful result directly in the authenticated POST response;
- do not redirect;
- do not put the voucher in query parameters;
- do not mark the claim as failed;
- do not release the claimed voucher.

## Accepted risks for Free 1.0

The following do not require additional implementation in Sprint 8:

- one final claim racing with Pool deactivation;
- exact real-time remaining count across concurrent requests;
- browser clipboard API being unavailable;
- administrator intentionally opening multiple valid forms and distributing from each distinct intent.

The plugin must prevent accidental replay of the same intent, not prohibit an administrator from intentionally performing multiple distributions.

## Required regression tests

Protect:

- capability and nonce remain mandatory;
- inactive and missing Pools cannot distribute;
- same intent token cannot execute twice;
- two distinct intent tokens can distribute two distinct codes;
- atomic repository still uses transaction and `FOR UPDATE`;
- failed code-state update rolls back;
- empty Pool returns no code;
- completed Activity failure does not hide a claimed voucher;
- remaining-count failure does not hide a claimed voucher;
- unknown remaining inventory is not represented as zero;
- result tokens are unique;
- result token is scoped to current user;
- result is consumed once;
- two successful distributions for one user cannot overwrite each other;
- voucher never appears in redirect URL;
- voucher never appears in Activity context;
- result-store failure uses direct protected presentation;
- result-store failure does not release or reassign the voucher;
- refresh of redirected GET cannot distribute;
- database version remains 2 unless persistence architecture genuinely requires schema change.

## Recommended implementation sequence

### Sprint 8 Part 5.1 — Distribution Claim Outcome Hardening

- contain remaining-count failure;
- contain completed Activity failure;
- make successful claim authoritative;
- add unknown-remaining presentation.

### Sprint 8 Part 5.2 — Distribution Intent Idempotency

- one-use intent token;
- duplicate POST rejection;
- double-click and browser-resubmit protection.

### Sprint 8 Part 5.3 — One-time Result Delivery Hardening

- unique opaque result token;
- per-user ownership;
- consume-once result store;
- multi-tab safety;
- checked persistence;
- direct protected fallback when storage fails.

### Sprint 8 Part 5.4 — Final Distribution Safety Review

- full regression chain;
- WordPress multi-tab smoke test;
- double-submit smoke test;
- privacy review;
- Sprint 8 release review decision.

## Files-to-delete review

Delete now:

`none`

Potential replacement:

The direct per-user transient logic in `templates/admin/distribution.php` and `DistributionAdmin.php` should be refactored in place during Part 5.3. No file needs to be deleted merely to perform the hardening.

## Final decision

**Distribution is not yet approved as the final Free 1.0 safety baseline.**

The database claim boundary is strong. The remaining risk is post-claim delivery semantics: once a voucher has been atomically assigned, optional operational metadata or one-time result persistence must never make that successful claim look failed or make the voucher value disappear.

Proceed with **Sprint 8 Part 5.1 — Distribution Claim Outcome Hardening**.
