# Voucher Manager 0.9.0-alpha — The Product Language

Sprint 9 Part 2 establishes the English source-language contract for the path to Free 1.0.

## Highlights

- Use `One-Time Code` and `One-Time Codes` consistently as the user-facing domain object.
- Retain concise workflow actions such as `Import Codes`, `Distribute Code` and `Copy Code`.
- Replace unnecessary direct address with neutral-professional wording.
- Improve privacy copy by distinguishing complete One-Time Code values from masked Inventory references.
- Use translation-ready singular and plural forms for remaining inventory.
- Document the approved German target term `Einmalcode`.
- Preserve all Sprint 8 lifecycle and Distribution safety boundaries.
- Preserve VM-018 so an intentionally blocked rollback records only the yellow `import.rollback_blocked` event.

## Technical boundary

This release changes user-facing English copy and its regression coverage.

It does not rename:

- the `VoucherManager` PHP namespace;
- the `voucher-manager` text domain;
- `voucher_manager_*` hooks or options;
- database tables;
- persisted status or event values;
- stable services or repositories.

## Upgrade boundary

No database schema migration is introduced. `VOUCHER_MANAGER_DATABASE_VERSION` remains `2`.

## Validation

The candidate must pass:

- the complete Composer Quality Gate;
- Product Language regression coverage;
- VM-018 Rollback Activity cleanup coverage;
- PHP 8.1 through 8.4 in GitHub Actions;
- release-artifact validation;
- a complete English WordPress UI walkthrough.
