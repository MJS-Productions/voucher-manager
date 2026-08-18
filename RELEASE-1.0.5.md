# Voucher Manager 1.0.5 — Activity and Terminology Consistency

Professional One-Time Code Management for WordPress.

Voucher Manager 1.0.5 is a maintenance release that aligns Activity context and user-facing terminology discovered during the User Documentation review.

## Improvements

- Preserves the Pool name before permanent Pool deletion and displays it in the resulting Activity entry.
- Keeps the internal Pool ID as a fallback when no Pool name is available in Activity context.
- Hides `Undo import` for completed Imports that already contain distributed One-Time Codes.
- Keeps the protected server-side rollback validation in place as the authoritative safety boundary.
- Aligns German Import undo wording with `rückgängig machen` / `Rückgängig gemacht`.
- Uses `One-Time Codes` / `Einmalcodes` in descriptive uninstall copy for the managed data object.

## Validation

- Added regression coverage for Pool-name preservation during successful and failed Pool deletion.
- Added presentation coverage that prevents rollback review when assigned codes exist.
- Extended German language polish coverage for Import undo and uninstall terminology.
- Requires the GitHub Quality Gate to pass before release packaging.

## Upgrade boundary

No database schema migration is introduced. Existing pools, imports, inventory, Activity history and retention settings remain unchanged.

## Compatibility

- WordPress 6.5 or newer
- PHP 8.1 or newer
- MySQL or MariaDB

Made in Austria by MJS-Productions.
