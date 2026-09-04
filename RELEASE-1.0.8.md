# Voucher Manager 1.0.8 — WordPress 7.1 and Release Hardening

Professional One-Time Code Management for WordPress.

Voucher Manager 1.0.8 is a maintenance release focused on WordPress 7.1 compatibility, WordPress.org release hardening and a small Import guidance correction.

## WordPress 7.1

- Confirmed Voucher Manager operation under WordPress 7.1.
- Updated the WordPress.org `Tested up to` declaration to 7.1.
- Core administration workflows were exercised under WordPress 7.1, including Pool deletion, Pool editing, Pool activation, code import and code distribution.

## Release and Plugin Check hardening

- Hardened database query preparation for current WordPress Plugin Check expectations while preserving existing application behavior.
- Updated integration assertions that had encoded older SQL implementation details so they continue to verify the intended privacy and lifecycle boundaries.
- Tightened the release builder to ship production runtime files only.
- Strengthened release validation so development-only repository files are rejected from the installable artifact.
- Kept German translation artifacts in the repository for maintenance and verification while moving production translation delivery to WordPress.org language packs.

## Import guidance

- Corrected the Import help text to match actual blank-row behavior.
- Blank rows are ignored entirely.
- Invalid rows are counted but not imported.
- Updated and verified the English and German interface text.

## Validation

- GitHub Quality Gate passes on the release preparation baseline.
- WordPress Plugin Check completes with zero errors.
- Remaining Plugin Check warnings were reviewed individually where security-relevant and are accepted when they reflect deliberate database/repository architecture or static-analysis limitations.
- English and German Import guidance was manually verified after the translation update.

## Upgrade boundary

No database schema migration is introduced. Existing pools, imports, inventory, Activity history, retention settings and One-Time Codes remain unchanged.

## Compatibility

- WordPress 6.5 or newer
- Tested up to WordPress 7.1
- PHP 8.1 or newer
- MySQL or MariaDB

Made in Austria by MJS-Productions.
