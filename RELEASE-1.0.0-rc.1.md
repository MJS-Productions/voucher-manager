# Voucher Manager 1.0.0-rc.1 — The First Release Candidate

**Professional One-Time Code Management for WordPress**

Voucher Manager Free 1.0 has reached its first public release candidate.

## What this candidate delivers

- Pools for organizing campaign, product or workflow-specific One-Time Codes.
- TXT and CSV imports with duplicate protection, clear result summaries and all-or-nothing rollback.
- Atomic, replay-safe manual Distribution that prevents duplicate assignment under rapid or concurrent requests.
- Privacy-safe Inventory and Activity views without exposing complete One-Time Code values.
- Bounded Activity retention through WordPress Cron.
- Explicit, consent-aware uninstall data deletion with data preservation as the default.
- Complete English and German administration experiences.
- Deterministic PO-to-MO translation compilation and validated release artifacts.
- Automated Quality Gate across PHP 8.1, 8.2, 8.3 and 8.4.

## Product identity

- Product: **Voucher Manager**
- Tagline: **Professional One-Time Code Management for WordPress**
- Pro family name: **Voucher Manager Pro**
- Slug and text domain: `voucher-manager`

## Upgrade boundary

The database schema remains version `2`. No database schema migration is introduced by this release candidate.

Existing Pools, Imports, One-Time Codes, Activity and Settings from the reviewed Alpha line remain in place.

## Release-candidate test focus

Test the validated `dist/voucher-manager.zip` artifact as a normal user would:

- clean installation;
- upgrade from `0.8.0-alpha` or the latest internal development build;
- English and German administration;
- Import and protected rollback;
- Distribution, rapid double-click and replay behavior;
- Inventory and Activity filters;
- Activity retention;
- deactivation and reactivation;
- opt-in uninstall deletion on a disposable test installation.

## Known release status

This is a release candidate, not the final `1.0.0` release. New features are frozen. Only release-blocking defects and necessary documentation corrections should change before final release.
