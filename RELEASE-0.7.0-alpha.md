# Voucher Manager 0.7.0-alpha — The Visible Inventory

Sprint 7 introduces a privacy-safe Pool Inventory experience for administrators.

## Highlights

- Open Inventory directly from a Pool card while Voucher Manager and Pools remain in the correct WordPress navigation context.
- Review pool-wide totals and filtered Inventory results separately.
- Filter public Inventory states by Available or Assigned and narrow results to a source import.
- Browse deterministic 50-row pagination with visible result ranges.
- See masked voucher references without loading complete voucher values into the Inventory read model.
- Review sanitized import provenance such as `Import #12 — codes.csv`.
- See WordPress-local import and assignment timestamps.
- Surface contradictory lifecycle timestamps as read-only attention states.
- Use contextual empty states and `Reset filters` only when filters are active.
- Activity now follows the same conditional filter-reset interaction rule.

## Privacy and write boundary

Inventory remains read-only.

It does not provide Copy, Reveal, Export, manual state changes, bulk actions or automatic lifecycle repair. Complete voucher values are excluded from the Inventory read model and template.

## Upgrade boundary

No database schema migration is introduced. `VOUCHER_MANAGER_DATABASE_VERSION` remains `2`.

## Validation

The candidate must pass the complete Composer Quality Gate, GitHub Actions on PHP 8.1 through 8.4, validated release-artifact checks and the Sprint 7 WordPress smoke-test matrix before publication.
