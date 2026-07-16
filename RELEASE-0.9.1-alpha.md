# Voucher Manager 0.9.1-alpha — The Translation Foundation

Sprint 9 Part 3.1 establishes the runtime internationalization foundation for the path to the first German translation.

## Highlights

- Route user-visible Distribution result messages through the `voucher-manager` text domain.
- Remove hard-coded English JavaScript fallbacks from the Distribution copy interaction.
- Reuse translated HTML labels inside JavaScript.
- Add a documented boundary between interface copy and untranslated technical diagnostics.
- Add an automated Internationalization Readiness gate.
- Preserve the Product Language contract and VM-018 Rollback Activity semantics.

## Staged localization scope

This release intentionally does not yet include:

- a generated POT catalog;
- the complete `_x()` context review;
- the complete placeholder and plural audit;
- `de_DE` PO/MO files.

Those stages follow in Sprint 9 Parts 3.2 and 3.3.

## Technical boundary

Stable namespaces, hooks, option names, database identifiers, event keys, stored statuses and internal exception diagnostics remain unchanged.

## Upgrade boundary

No database schema migration is introduced. `VOUCHER_MANAGER_DATABASE_VERSION` remains `2`.

## Validation

The candidate must pass:

- the complete Composer Quality Gate;
- Product Language coverage;
- VM-018 Rollback Activity cleanup coverage;
- Internationalization Readiness coverage;
- PHP 8.1 through 8.4 in GitHub Actions;
- release-artifact validation;
- an English WordPress smoke test of Distribution messages and copy-label behavior.
