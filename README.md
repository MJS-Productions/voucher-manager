# Voucher Manager

Professional management and distribution of unique voucher codes for WordPress.

> Simple for users. Reliable for businesses.

**Status:** Alpha development  
**Release candidate:** `0.8.1-alpha` — The Activity Clarity Patch  
**Previous published release:** `0.8.0-alpha` — The Hardened Lifecycle

The `0.8.1-alpha` maintenance candidate preserves the complete Sprint 8 lifecycle and Distribution safety baseline while correcting Activity semantics for intentionally blocked import rollbacks.

## Current capabilities

- dashboard metrics, quick actions and privacy-safe recent operational activity;
- pool creation, editing, activation and inventory-oriented status presentation;
- privacy-safe, pool-scoped Inventory with masked references, filters, pagination and import provenance;
- read-only lifecycle-integrity visibility for contradictory Inventory timestamps;
- pool lifecycle Danger Zone for scoped available-code deletion and atomic full pool deletion;
- TXT and CSV voucher-code imports with result summaries and protected rollback;
- atomic manual distribution from `available` to `assigned`;
- one-use Distribution intents that prevent duplicate execution from rapid resubmission;
- authoritative post-claim outcomes and isolated one-time result delivery under concurrent browser requests;
- bounded Activity retention and consent-aware uninstall behavior;
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

The Quality Gate validates PHP syntax, plugin structure, version consistency, autoloading, the core Golden Path, code-state integrity, operational logging, lifecycle boundaries and the complete Sprint 8 Distribution safety invariants. It also builds and validates `dist/voucher-manager.zip`.

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
- `docs/SPRINT_8_PART_5_4_FINAL_DISTRIBUTION_SAFETY_REVIEW.md` — final Distribution invariants and Keeper smoke-test gate.

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
