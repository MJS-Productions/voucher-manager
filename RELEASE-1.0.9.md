# Voucher Manager 1.0.9 — Extension APIs and Capability Foundation

Professional One-Time Code Management for WordPress.

Voucher Manager 1.0.9 establishes the supported foundation required by extensions while preserving Voucher Manager's administrator-only standalone behavior.

## Extension APIs

- Added supported APIs for Distribution, Pool and Inventory reads, Activity queries and Activity presentation.
- Added an Activity retention archive handoff boundary so extensions can integrate with retention processing without duplicating Voucher Manager-owned behavior.
- Added inventory-change notifications, including successful deletion of available One-Time Codes.
- Added a Pool warning-threshold change event so extensions can react after an existing Pool threshold actually changes.
- Extension event delivery contains listener failures so extension errors do not replace successful Voucher Manager operations.

## Capability foundation

- Added dedicated Voucher Manager capabilities for granular access control.
- Preserved secure administrator access as the standalone Voucher Manager behavior.
- Added an explicit extension delegation boundary so an authorized extension can enable delegated access without introducing a parallel role or user-management system.
- Made Dashboard actions, metrics, recent Activity and relevant internal navigation respect the capabilities granted to the current user.
- Kept server-side capability checks authoritative for protected pages and actions.

## Activity presentation

- Added a supported Activity presentation API for reusable human-readable and translatable Activity labels and details.
- Unified Activity detail formatting across event types, including Distribution Pool and remaining-inventory context.

## Quality and localization

- Added `mjs-productions/mjs-quality` v0.2.1 as the shared engineering and quality development dependency.
- Added a dedicated real-MySQL Q5 concurrency test for the atomic Distribution claim path using two independent PHP workers and the WordPress `wpdb` layer.
- Migrated localization generation and validation to the shared quality workflow.
- Added independent Localization CI and a manual localization-artifact workflow.
- Repository POT, PO and MO artifacts remain available for development and translation maintenance while production translations continue to be delivered through WordPress.org language packs.

## WordPress.org presentation

- Refined WordPress.org terminology, discovery tags and product wording.
- Added a development notice and public information link for Voucher Manager Pro.
- Corrected the 1.0.8 translation-delivery history to reflect WordPress.org language-pack distribution.

## Validation

- GitHub Quality Gate covers PHP 8.1, 8.2, 8.3 and 8.4.
- Dedicated Q5 verification confirms that two concurrent workers competing for one available One-Time Code produce exactly one successful claim.
- Extension API, capability, Activity presentation, inventory-change and Pool warning-threshold integration coverage is included.
- Localization artifact freshness and German translation completeness are validated independently.
- The installable release artifact is built and validated by GitHub Actions.

## Upgrade boundary

No database schema migration is introduced. Existing pools, imports, inventory, Activity history, retention settings and One-Time Codes remain unchanged.

Voucher Manager continues to operate as an administrator-only plugin when no authorized extension enables delegated access.

## Compatibility

- WordPress 6.5 or newer
- Tested up to WordPress 7.1
- PHP 8.1 or newer
- MySQL or MariaDB

Made in Austria by MJS-Productions.
