# Sprint 9 Part 3.1 — Internationalization Audit

## Verdict

**PASS after cleanup.**

The runtime UI uses the `voucher-manager` text domain consistently. Sprint 9 Part 3.1 closes the remaining high-confidence translation gaps without changing behavior.

## Findings corrected

### User-visible domain messages

`DistributionService` returned three English result messages as raw literals:

- Pool unavailable
- No available One-Time Codes
- Distribution completed

These messages can be rendered by the administration UI and are now passed through `__()` with the plugin text domain.

Technical exception messages remain untranslated because they are diagnostic boundaries and are not intentionally presented as interface copy.

### JavaScript fallbacks

The Distribution copy interaction contained hard-coded English fallbacks for `Copied` and `Copy code`.

The script now receives both labels exclusively from translated `data-*` attributes. Its fallback is the already translated button text rather than another English literal.

### Text domain

Runtime translation calls use:

`voucher-manager`

Test stubs and test-output strings are excluded from the runtime localization contract.

## Boundaries

Part 3.2 completed:

- `_x()` context for ambiguous menu, status, filter and table labels;
- translator comments for dynamic placeholders;
- `_n()` handling for import, rollback and destructive count-sensitive copy;
- localization and translator documentation.

Still pending for Part 3.3:

- generate the POT catalog;
- ship complete `de_DE` PO/MO files;
- perform the German WordPress experience gate.

## Technical identifiers

The following remain unchanged and untranslated:

- PHP namespaces and class names
- hooks and option names
- database identifiers
- event names
- stored status values
- exception diagnostics

## Database

No migration is introduced. Database version remains `2`.
