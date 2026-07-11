# Changelog

## Unreleased

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
