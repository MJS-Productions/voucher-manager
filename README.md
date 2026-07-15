# Voucher Manager

Professional management and distribution of unique voucher codes for WordPress.

> Simple for users. Reliable for businesses.

**Status:** Alpha development  
**Release candidate:** `0.7.0-alpha` — The Visible Inventory  
**Previous published release:** `0.6.0-alpha` — The First Experience

Sprint 7 is development-complete and has entered final release validation. The `0.7.0-alpha` candidate adds a privacy-safe, pool-scoped Inventory experience with filters, pagination, import provenance and read-only lifecycle-integrity visibility without introducing a database migration.

## Current capabilities

- dashboard metrics, quick actions and privacy-safe recent operational activity;
- pool creation, editing, activation and inventory-oriented status presentation;
- privacy-safe, pool-scoped Inventory with masked references, filters, pagination and import provenance;
- read-only lifecycle-integrity visibility for contradictory Inventory timestamps;
- pool lifecycle Danger Zone for scoped available-code deletion and atomic full pool deletion;
- TXT and CSV voucher-code imports with result summaries and protected rollback;
- atomic manual distribution from `available` to `assigned`;
- one-time distribution result presentation with remaining-inventory guidance;
- privacy-aware operational logging with stable dotted event names;
- centralized code-state transition protection;
- bounded admin error handling for critical operations;
- automated Quality Gate across PHP 8.1, 8.2, 8.3 and 8.4;
- validated WordPress release artifacts built by the project quality workflow.

## Core workflow

1. Create a pool.
2. Import voucher codes.
3. Distribute one available code.
4. Track inventory and operational activity.

Voucher Manager currently prioritizes stability and usability before larger integrations or commercial features.

## Quality and release process

The full local project quality chain is:

```bash
composer install
composer quality
```

The Quality Gate validates PHP syntax, plugin structure, version consistency, autoloading, the core Golden Path, code-state integrity, operational logging and the current Sprint 7 Inventory and administration experiences. It also builds and validates `dist/voucher-manager.zip`.

Official WordPress smoke tests should use the `voucher-manager.zip` artifact built and validated by GitHub Actions rather than a manually packed source archive.

## Requirements

- WordPress 6.5 or newer
- PHP 8.1 or newer
- Composer 2.x for development

## Project documentation

- `CHANGELOG.md` — release and unreleased development changes;
- `BUG_STORIES.md` — notable project failures and lessons learned;
- `docs/` — current technical and experience documentation;
- `docs/adr/` — architecture decision records;
- `docs/SPRINT_7_RELEASE_READINESS.md` — Sprint 7 Inventory boundary, smoke-test matrix and release-review gate.

## Credits

Voucher Manager is a project by **MJS-Productions e.U.**

Originally conceived from a real-world business need by Michael Sczaszny.

Designed, architected and developed collaboratively with ChatGPT (OpenAI).

Made with ❤️ in Austria.

## Philosophy

Simple things deserve professional solutions.

Every great project starts with a single line.

If Voucher Manager saves you time, then it has achieved exactly what it was created for.

## License

GPL-2.0-or-later.
