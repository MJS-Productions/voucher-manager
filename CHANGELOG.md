# Changelog

## Unreleased

### Fixed
- Added the missing dedicated confirmation step before deleting available codes.
- Added regression coverage that rejects direct or unacknowledged available-code deletion.

### Added
- Sprint 6 Part 2.1 pool lifecycle and dedicated Danger Zone.
- Scoped deletion of available codes with privacy-safe operational logging.
- Atomic full pool deletion for pool, codes and import records with rollback on failure.
- Exact pool-name confirmation for permanent full deletion.
- Automated pool lifecycle integrity test and ADR 0012.


### Added
- Sprint 6 Part 2 pool experience.
- Pool inventory overview with available, distributed and total code counts.
- Ready, low-stock, empty and inactive pool presentation states.
- Context-aware pool actions and a guided empty state.
- Automated pool experience test.
- ADR 0011 and pool experience documentation.


### Added
- Sprint 6 Part 1 navigation and dashboard experience.
- Dashboard inventory metrics for available and distributed codes.
- Quick actions for pool creation, import and distribution.
- Human-readable recent operational activity.
- Automated dashboard experience test.
- ADR 0010 and dashboard documentation.

### Changed
- Renamed the duplicated Voucher Manager submenu item to Dashboard.


## 0.5.0-alpha - 2026-07-13

**The Stable Foundation**

### Added
- Sprint 5 Part 5 error boundaries and operational logging.
- Stable operational event and severity vocabularies.
- Privacy-aware context sanitization.
- Automated operational logging integration test.
- ADR 0009 and operational logging documentation.


### Added
- Sprint 5 Part 4 centralized code-state model.
- CodeStateMachine with explicit allowed transitions.
- Automated state-integrity test.
- ADR 0008 and state-model documentation.


### Added
- Sprint 5 Part 3 golden path integration test.
- Failure strategy for recoverable and unexpected errors.
- Automated validation of import, distribution, logging and rollback protection.
- Sprint 5 Part 2 quality workflow integration.
- PHP 8.4 CI coverage.
- Autoload, structure, header and release-artifact validation.
- Validated release ZIP as a GitHub Actions artifact.

## 0.4.1-alpha - The Quality Patch

### Added
- Release hardening documentation.
- Additional quality checks.

### Fixed
- Improved release preparation after 0.4.0-alpha.1 lessons.

# Changelog

## Unreleased

### Added
- Sprint 5 Part 2 quality workflow integration.
- PHP 8.4 CI coverage.
- Autoload, structure, header and release-artifact validation.
- Validated release ZIP as a GitHub Actions artifact.

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

# Changelog

## Unreleased

### Added
- Sprint 5 Part 2 quality workflow integration.
- PHP 8.4 CI coverage.
- Autoload, structure, header and release-artifact validation.
- Validated release ZIP as a GitHub Actions artifact.

## 0.3.0-alpha - The First Import

### Added

- Streaming TXT import with one code per line.
- CSV import using the first column with common delimiter detection.
- Duplicate prevention inside files and existing pools.
- Bounded database batches for large imports.
- Import history, statistics, logging, and reversible imports.
- Database schema version 2 with import records.
- Import dashboard metric and administration page.
- Import architecture and branding ADRs.

## 0.2.0-alpha.1 - The First Pool

- Pool administration and database lifecycle.
