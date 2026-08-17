# Voucher Manager 1.0.4 — Import Activity Pool Context Fix

Professional One-Time Code Management for WordPress.

Voucher Manager 1.0.4 aligns completed Import entries with the existing Activity presentation by recording and displaying the Pool name instead of exposing the internal Pool ID.

## Improvements

- Records the Pool name in newly completed Import Activity context.
- Displays `Pool: <name>` for newly completed Import entries.
- Preserves the existing `Pool #<id>` fallback for Activity entries recorded before 1.0.4.
- Keeps One-Time Code values and personal data excluded from Activity history.

## Validation

- Added regression coverage for Pool-name presentation on completed Import Activity entries.
- Added regression coverage for the legacy Pool-ID fallback.
- Extended the Golden Path to verify that completed Imports preserve the Pool name in Activity context.
- Requires the GitHub Quality Gate to pass before release packaging.

## Upgrade boundary

No database schema migration is introduced. Existing pools, imports, inventory, Activity history and retention settings remain unchanged. Existing Import Activity entries without a stored Pool name continue to display their Pool ID.

## Compatibility

- WordPress 6.5 or newer
- PHP 8.1 or newer

Made in Austria by MJS-Productions.
