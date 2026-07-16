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

Part 3.1 does not yet:

- generate the POT catalog;
- add `_x()` context to ambiguous labels;
- perform the complete placeholder and plural audit;
- ship `de_DE` PO/MO files.

Those belong to Parts 3.2 and 3.3.

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
