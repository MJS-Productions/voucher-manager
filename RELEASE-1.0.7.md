# Voucher Manager 1.0.7 — Activity History Coverage

Professional One-Time Code Management for WordPress.

Voucher Manager 1.0.7 expands privacy-safe Activity History coverage across administrative, lifecycle and maintenance operations and includes additional import and terminology refinements.

## Activity History

- Records Pool creation, updates, activation and deactivation.
- Records Settings changes without storing unnecessary setting values.
- Uses dedicated failure Activity for unexpected Distribution execution failures.
- Records plugin installation, activation and deactivation.
- Records uninstall when Voucher Manager data is retained, allowing the lifecycle history to remain understandable after a later reinstall.
- Records successful automatic Activity cleanup only when old entries were actually deleted.
- Records failed automatic Activity cleanup as an error.

## Privacy and failure boundaries

- One-Time Code values remain excluded from Activity History.
- Exception messages and stack traces remain excluded from Activity History.
- Cleanup and Distribution failure entries retain only bounded operational context.

## Additional improvements

- TXT imports ignore blank lines instead of treating them as importable values.
- Administrator-facing Activity metrics and deletion confirmations use clearer English and German wording.
- One-Time Code / Einmalcode terminology is used more consistently in the administration interface.

## Validation

- Expanded integration coverage for Activity logging, lifecycle behavior, automatic cleanup, Distribution failure handling, TXT blank-line imports and German translations.
- Requires the GitHub Quality Gate to pass before release packaging.

## Upgrade boundary

No database schema migration is introduced. Existing pools, imports, inventory, Activity history and retention settings remain unchanged.

## Compatibility

- WordPress 6.5 or newer
- PHP 8.1 or newer
- MySQL or MariaDB

Made in Austria by MJS-Productions.
