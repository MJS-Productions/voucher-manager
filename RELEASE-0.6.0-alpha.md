# Voucher Manager 0.6.0-alpha — The First Experience

Sprint 6 turns the stable technical foundation into a coherent WordPress administration experience.

## Highlights

- Dashboard metrics, quick actions and human-readable Recent Activity.
- Inventory-oriented Pool Experience.
- Dedicated Pool Danger Zone with reviewed destructive actions.
- Guided TXT and CSV import with clear result summaries.
- Protected import rollback review.
- Inventory-aware manual distribution with a one-time voucher result and Copy code action.
- Dedicated filtered and paginated Operational Activity history.
- Actionable guidance for warnings and failures without exposing raw log context.
- Cross-layer regression protection for destructive confirmation boundaries and stable operational event vocabulary.
- Complete Quality Gate on PHP 8.1, 8.2, 8.3 and 8.4.
- Validated WordPress release artifact built by GitHub Actions.

## The First Experience

The primary administration workflow is now presented as one connected experience:

1. Create and understand a pool.
2. Import voucher codes and review the result.
3. Distribute one available code safely.
4. Track inventory and operational activity.
5. Review lifecycle actions before permanent deletion.

## Safety and privacy

Voucher Manager continues to protect the core state model and operational privacy boundary.

- Distribution atomically claims an available code before marking it assigned.
- Assigned codes block unsafe import rollback.
- Available-code deletion requires a dedicated review and acknowledgement.
- Full pool deletion requires exact pool-name confirmation and uses atomic repository deletion.
- Operational Activity does not present voucher values, raw context, exception messages, stack traces or personal data.
- Distribution results are one-time presentation data and are consumed after rendering.

## Regression lessons

Sprint 6 manual WordPress smoke tests found three cross-layer regressions:

- VM-008 — The Danger Zone Side Door
- VM-009 — The Activity That Forgot Its Name
- VM-010 — The Dots That Disappeared

All three were fixed and converted into automated regression protection before this release review.

## Release validation

Before publishing the GitHub Release:

1. Run `composer quality`.
2. Confirm GitHub Quality is green on PHP 8.1, 8.2, 8.3 and 8.4.
3. Use the `voucher-manager.zip` artifact from the successful PHP 8.4 Quality job.
4. Install it as an update over `0.5.0-alpha`.
5. Complete the Sprint 6 smoke-test matrix in `docs/SPRINT_6_RELEASE_READINESS.md`.
6. Confirm WordPress reports version `0.6.0-alpha`.
7. Publish the GitHub Release only after the final artifact passes.

## Upgrade notes

No database schema migration is introduced by `0.6.0-alpha`. Existing pools, imports, codes and operational logs should remain available after the update.

## Known scope

`reserved`, `expired` and `cancelled` remain defined domain states but are not yet exposed as user-facing workflows.
