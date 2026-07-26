# Voucher Manager 1.0.2 — Activity Pool Context Fix

Professional One-Time Code Management for WordPress.

Voucher Manager 1.0.2 corrects the successful-distribution entry in Activity history.

## Improvements

- Shows the Pool name for newly recorded successful distributions.
- Shows the remaining inventory with correct singular and plural wording.
- Removes the internal event key from the administrator-facing Activity list.
- Preserves the privacy boundary: One-Time Code values and personal data are never recorded in Activity.

## Upgrade boundary

No database schema migration is introduced. Existing activity records remain readable; Pool names appear on newly recorded successful distributions.

## Compatibility

- WordPress 6.5 or newer
- PHP 8.1 or newer
