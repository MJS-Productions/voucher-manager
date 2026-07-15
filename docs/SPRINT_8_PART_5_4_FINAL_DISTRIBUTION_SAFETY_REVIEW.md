# Sprint 8 Part 5.4 — Final Distribution Safety Review

## Verdict

**PASS — ready for Keeper concurrency and lifecycle smoke testing.**

The current manual Distribution path has one authoritative write boundary: the atomic `available → assigned` database claim. Request idempotency, post-claim containment and result delivery now preserve that boundary instead of competing with it.

## Reviewed lifecycle

1. Distribution page renders only active Pools with available inventory.
2. Every rendered form receives a unique, owner-scoped, ten-minute intent.
3. Capability and WordPress nonce checks run before intent consumption.
4. The intent is consumed before the Distribution Service can claim a voucher.
5. The repository claims the oldest available row inside a transaction and `FOR UPDATE`.
6. A committed claim is authoritative.
7. Remaining-inventory and Activity failures cannot hide or release the claimed voucher.
8. The result is stored behind a unique, owner-scoped, short-lived delivery token.
9. A racing replay cannot claim again.
10. A racing replay receives an independent delivery token for the same authoritative result.
11. Every delivery token is consume-once.
12. Result-store failure after a committed claim renders the voucher directly in the protected POST response.

## Failure matrix

| State / failure | Voucher claim | Result behavior |
| --- | --- | --- |
| Form never submitted | none | intent expires |
| Invalid capability | none | access denied |
| Invalid nonce | none | WordPress rejects request |
| Malformed / foreign intent | none | replay notice; no claim |
| Expired / consumed intent | none | recover delivery if authoritative result exists; otherwise replay notice |
| Pool missing or inactive at POST | none | failure result |
| Pool emptied before POST | none | empty result |
| Concurrent claim of same last row | one winner only | loser receives empty result |
| Remaining count fails after commit | committed | voucher shown; remaining unknown |
| Activity write fails after commit | committed | voucher shown |
| Result store fails after commit | committed | direct protected voucher presentation |
| Double / spam click, same intent | one claim maximum | independent delivery tokens show same authoritative result |
| Two tabs, different intents | independent claims | independent result tokens |
| Result token refreshed | no claim | consumed result is not shown again |
| Result token opened by another admin | no claim | result not disclosed |

## Privacy review

- Voucher values are not stored in Distribution intent state.
- Voucher values are not written to Activity context.
- Voucher values are not written to post-claim error messages.
- Redirect URLs contain opaque result tokens, never voucher values.
- Result and intent-result options are non-autoloaded and short-lived.
- Result consumption is owner-scoped.
- Runtime Distribution state is removed during uninstall.

The short-lived intent-result recovery mapping intentionally retains the authoritative result payload for replay recovery. This includes the voucher value for at most the result TTL. It is owner-scoped, non-autoloaded, addressed by a hash of the opaque intent and exists only to prevent a racing browser response from hiding an already committed voucher.

## Concurrency invariants

### Invariant 1

One Distribution intent can cause **at most one call path past successful intent consumption**.

### Invariant 2

One available voucher row can be successfully transitioned to assigned **at most once**.

### Invariant 3

Once a voucher claim commits, later metadata, Activity or result-delivery failures cannot convert the business outcome into an unclaimed voucher.

### Invariant 4

Multiple browser responses for the same intent may receive multiple delivery tokens, but every delivery contains the **same authoritative result** and does not execute another claim.

### Invariant 5

Separate forms receive separate intents and remain valid deliberate Distribution actions.

## Keeper smoke-test gate

The final manual gate is intentionally hostile:

1. Normal single click.
2. Rapid double click.
3. Rapid spam click.
4. Last voucher plus rapid double click.
5. Last voucher plus spam click.
6. Two tabs, same Pool.
7. Two tabs, different Pools.
8. Refresh after successful result.
9. Browser Back after successful result, if the browser restores the form.
10. Open a Pool Distribution link and verify Pool preselection.

For every same-intent click race, compare Inventory before and after. Available inventory must fall by exactly one and the visible voucher must match the single assigned outcome.

## Database

No schema migration is introduced. Database version remains `2`.
