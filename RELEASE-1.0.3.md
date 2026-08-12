# Voucher Manager 1.0.3 — Distribution Result Boundary Fix

Professional One-Time Code Management for WordPress.

Voucher Manager 1.0.3 hardens the manual Distribution workflow against accidental rapid resubmission and updates the Dashboard credit.

## Improvements

- Separates a completed Distribution result from the next Distribution form.
- Adds an explicit `Distribute another One-Time Code` action before a new Distribution intent is created.
- Preserves the existing one-use Distribution intent protection for repeated submissions of the same form.
- Updates the Dashboard credit to `Made in Austria by MJS-Productions.`

## Validation

- Verified the revised Distribution flow on WordPress 6.5 with PHP 8.1.
- Verified equivalent behavior on the current WordPress and PHP test stack.
- Confirmed that an accidental double-click distributes only one One-Time Code.
- Confirmed the GitHub Quality Gate before release preparation.

## Upgrade boundary

No database schema migration is introduced. Existing pools, imports, inventory, Activity history and retention settings remain unchanged.

## Compatibility

- WordPress 6.5 or newer
- PHP 8.1 or newer

Made in Austria by MJS-Productions.
