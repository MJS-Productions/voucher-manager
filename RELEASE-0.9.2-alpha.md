# Voucher Manager 0.9.2-alpha — The Localization Contract

Sprint 9 Part 3.2 completes the localization-quality contract before the first German catalog is shipped.

## Highlights

- Add translator context to ambiguous short labels such as admin menu names, One-Time Code statuses, filters and table columns.
- Add translator comments to dynamic placeholders.
- Use numbered placeholders for reorderable multi-value sentences.
- Use `_n()` for count-sensitive Import, rollback and destructive confirmation copy.
- Improve the Import result summary so singular counts no longer produce English phrases such as `1 codes`.
- Document the localization and translator contracts.
- Preserve the Product Language contract, VM-018 rollback semantics and all Distribution safety boundaries.

## Visible behavior

Most pages look unchanged. Count-sensitive messages are now grammatically correct for singular and plural values.

The Distribution confirmation wording changes from:

`One One-Time Code will be assigned immediately after confirmation.`

to:

`One-Time Code assignment occurs immediately after confirmation.`

## Remaining localization scope

Sprint 9 Part 3.3 will generate the source catalog and ship the complete German `de_DE` PO/MO translation.

## Upgrade boundary

No database schema migration is introduced. `VOUCHER_MANAGER_DATABASE_VERSION` remains `2`.

## Validation

The candidate must pass:

- the complete Composer Quality Gate;
- Product Language coverage;
- VM-018 Rollback Activity cleanup coverage;
- Internationalization Readiness coverage;
- Translation Readiness coverage;
- PHP 8.1 through 8.4 in GitHub Actions;
- release-artifact validation;
- a WordPress regression walkthrough of Import, Inventory, Distribution, Activity and destructive confirmations.
