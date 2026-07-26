# Voucher Manager

Professional One-Time Code Management for WordPress.

> Simple for users. Reliable for businesses.

**Status:** Stable Release  
**Current release:** `1.0.0` — Initial Stable Release  
**Previous release candidate:** `1.0.0-rc.2` — Final Polish Candidate

`1.0.0` is the first stable release of Voucher Manager Free. It combines the hardened One-Time Code lifecycle, complete German administration experience, deterministic translation artifacts and the fully audited release process under the official product identity.

## Current capabilities

- dashboard metrics, quick actions and privacy-safe recent operational activity;
- pool creation, editing, activation and inventory-oriented status presentation;
- privacy-safe, pool-scoped Inventory with masked references, filters, pagination and import provenance;
- read-only lifecycle-integrity visibility for contradictory Inventory timestamps;
- pool lifecycle Danger Zone for scoped available-code deletion and atomic full pool deletion;
- TXT and CSV One-Time Code imports with result summaries and protected rollback;
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
2. Import One-Time Codes.
3. Distribute one available code.
4. Track inventory and operational activity.

Voucher Manager Free is a complete product. Voucher Manager Pro will add convenience and advanced workflows without weakening the Free edition.

## Quality and release process

The full local project quality chain is:

```bash
composer install
composer translations
composer quality
```

The translation build deterministically compiles the current German PO catalog into the MO artifact used by WordPress. The Quality Gate then verifies translation freshness, PHP syntax, plugin structure, version consistency, autoloading, the core Golden Path, code-state integrity, operational logging, lifecycle boundaries and the complete Distribution safety invariants. It also builds and validates `dist/voucher-manager.zip`.

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
- `docs/GERMAN_TRANSLATION.md` — approved German terminology, catalog files and runtime loading.
- `docs/adr/0032-translation-artifact-integrity.md` — deterministic PO-to-MO build and release boundary.
- `docs/adr/0033-product-identity.md` — official product name, tagline and Free/Pro family.
- `docs/LOCALIZATION_GUIDE.md` — context, placeholder, plural and escaping rules.
- `docs/TRANSLATOR_NOTES.md` — product terminology and translation guidance.
- `docs/INTERNATIONALIZATION_AUDIT.md` — Sprint 9 Part 3.1 runtime string audit and staged localization boundary.
- `docs/PRODUCT_LANGUAGE.md` — approved English terminology, tone and German target language.
- `docs/SPRINT_8_PART_5_4_FINAL_DISTRIBUTION_SAFETY_REVIEW.md` — final Distribution invariants and Keeper smoke-test gate.

## Credits

Voucher Manager is a project by **MJS-Productions**

Originally conceived from a real-world business need by Michael Sczaszny.

Designed, architected and developed collaboratively with ChatGPT (OpenAI).

Made with ❤️ in Austria.

## Philosophy

Simple things deserve professional solutions.

Every great project starts with a single line.

If Voucher Manager saves you time, then it has achieved exactly what it was created for.

## License

GPL-2.0-or-later.
