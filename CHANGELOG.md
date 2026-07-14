# Changelog

## Unreleased

### Fixed
- Aligned Activity filter reset behavior with Pool Inventory by showing `Reset filters` only when Area or Outcome filtering is active.
- Kept Inventory accessible while preserving Voucher Manager → Pools context by hiding its registered submenu link visually instead of removing the page registration.

### Added
- Sprint 7 Part 4 import provenance and lifecycle-integrity visibility in Pool Inventory.
- Defensive timestamp and missing-import fallbacks without automatic data repair.
- ADR 0019 and expanded Inventory Experience regression coverage.
- Sprint 7 Part 3 contextual Inventory filters, result ranges and pagination guidance.
- Filter-aware empty states and responsive Inventory table presentation.
- ADR 0018 and expanded Inventory Experience regression coverage.
- Sprint 7 Part 2 read-only Pool Inventory screen.
- Pool-scoped masked references, state and import provenance visibility.
- Inventory state/import filters and 50-row pagination.
- Automated Inventory Experience test, ADR 0017 and Inventory documentation.

## 0.6.0-alpha - 2026-07-13 — The First Experience

### Added
- Sprint 6 Part 5 operational visibility with a dedicated filtered and paginated Activity screen.
- Actionable guidance for operational warnings and failures without exposing raw log context.
- Dashboard path from Recent Activity to the complete operational history.
- Automated operational visibility test, ADR 0015 and operational visibility documentation.
- Sprint 6 Part 4 distribution experience with inventory-aware pool selection.
- One-time distribution result presentation with an explicit copy action and remaining-inventory guidance.
- Guided empty-inventory state linking administrators back to Import.
- Automated distribution experience test, ADR 0014 and distribution experience documentation.
- Sprint 6 Part 3 guided import experience, human-readable result summaries and safe rollback review.
- Automated import experience test, ADR 0013 and import experience documentation.
- Sprint 6 Part 2.1 pool lifecycle and dedicated Danger Zone.
- Scoped deletion of available codes with privacy-safe operational logging.
- Atomic full pool deletion for pool, codes and import records with rollback on failure.
- Exact pool-name confirmation for permanent full deletion.
- Automated pool lifecycle integrity test and ADR 0012.
- Sprint 6 Part 2 pool experience with inventory overview and centralized presentation states.
- Automated pool experience test, ADR 0011 and pool experience documentation.
- Sprint 6 Part 1 navigation, dashboard metrics, quick actions and recent operational activity.
- Automated dashboard experience test, ADR 0010 and dashboard documentation.
- Sprint 6 final hardening coverage for event-vocabulary presentation, documentation consistency and release-readiness boundaries.
- Sprint 6 release-readiness documentation and smoke-test matrix.

### Changed
- Renamed the duplicated Voucher Manager submenu item to Dashboard.
- Consolidated the changelog into one chronological document without duplicated headings or stale Unreleased sections.

### Fixed
- Preserved the originating pool as a validated, changeable destination preselection when starting an import from a Pool card.
- Dashboard Recent Activity loading now preserves dots in stable event names so human-readable labels resolve correctly.
- Dashboard activity mappings now cover pool lifecycle events and privacy-safe import result details.
- Added the missing dedicated confirmation step before deleting available codes.
- Added regression coverage that rejects direct or unacknowledged available-code deletion.

## 0.5.0-alpha - 2026-07-13

**The Stable Foundation**

### Added
- Sprint 5 Part 5 error boundaries and operational logging.
- Stable operational event and severity vocabularies.
- Privacy-aware context sanitization.
- Automated operational logging integration test.
- ADR 0009 and operational logging documentation.
- Sprint 5 Part 4 centralized code-state model.
- `CodeStateMachine` with explicit allowed transitions.
- Automated state-integrity test and ADR 0008.
- Sprint 5 Part 3 Golden Path integration coverage for pool, import, distribution, logging and protected rollback.
- Sprint 5 Part 2 quality workflow integration.
- PHP 8.4 CI coverage in addition to PHP 8.1–8.3.
- Autoload, structure, header and release-artifact validation.
- Validated release ZIP as a GitHub Actions artifact.

## 0.4.1-alpha - The Quality Patch

### Added
- Release hardening documentation.
- Additional quality checks.

### Fixed
- Improved release preparation after the 0.4.0-alpha lessons.

## 0.4.0-alpha - The First Distribution

### Added
- Manual code distribution from active pools.
- Atomic code claiming to prevent duplicate distribution.
- Distribution logging and remaining-code count.
- Distribution admin screen.
- Bug Stories.

### Fixed
- WordPress update package identity.
- Release package no longer requires missing Composer dependencies at runtime.

## 0.3.0-alpha - The First Import

### Added
- Streaming TXT import with one code per line.
- CSV import using the first column with common delimiter detection.
- Duplicate prevention inside files and existing pools.
- Bounded database batches for large imports.
- Import history, statistics, logging and reversible imports.
- Database schema version 2 with import records.
- Import dashboard metric and administration page.
- Import architecture and branding ADRs.

## 0.2.0-alpha.1 - The First Pool

### Added
- Pool administration and database lifecycle.
