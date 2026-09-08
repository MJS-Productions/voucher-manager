# Voucher Manager 1.0.10 — Activity History Context Refinement

Professional One-Time Code Management for WordPress.

Voucher Manager 1.0.10 refines Activity History context so Pool-related events use available Pool names consistently while preserving safe fallbacks for legacy and failure entries.

## Activity History context

- Available-code deletion now records and displays the Pool name.
- Empty-Distribution Activity records the already-loaded Pool name when that defensive path is reached.
- Existing Pool ID fallbacks remain available for legacy and failure entries where a Pool name is not reliably available.
- Activity details remain concise and on one line.

## Privacy presentation

- Removed redundant per-event privacy guidance from Pool deletion events.
- Retained the global Activity privacy notice that One-Time Code values are not stored in Activity History.
- No One-Time Code values or sensitive data are added to Activity context.

## Validation

- Added regression coverage for Pool-name context, legacy Pool ID fallback and privacy-guidance presentation.
- Verified available-code deletion and full Pool deletion in WordPress using the installable artifact built by GitHub Actions.

## Upgrade boundary

No database schema migration is introduced. Existing pools, imports, inventory, Activity history, retention settings and One-Time Codes remain unchanged.

## Compatibility

- WordPress 6.5 or newer
- Tested up to WordPress 7.1
- PHP 8.1 or newer
- MySQL or MariaDB

Made in Austria by MJS-Productions.
