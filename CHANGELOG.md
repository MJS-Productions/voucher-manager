# Changelog

## Unreleased

## 0.9.1-alpha - 2026-07-16 — The Translation Foundation

### Added
- Sprint 9 Part 3.1 Internationalization Audit and runtime translation-boundary documentation.
- ADR 0029 defining the plugin text-domain requirement for visible runtime UI strings.
- Internationalization Readiness regression coverage before release build.

### Changed
- Routed user-visible Distribution result messages through the `voucher-manager` text domain.
- Removed duplicated English JavaScript fallbacks and reused translated copy-button labels from HTML data attributes.
- Preserved technical exception diagnostics, event keys and persisted values outside the translation catalog.

## 0.9.0-alpha - 2026-07-16 — The Product Language

### Added
- Product Language Guide defining `One-Time Code`, approved short action labels and neutral-professional tone.
- ADR 0028 for the Product Language contract.
- Product Language regression coverage for terminology, neutral wording and translation-ready plural forms.

### Changed
- Standardized English user-facing terminology around `One-Time Code` and `One-Time Codes`.
- Replaced direct-address result and onboarding copy with neutral-professional wording.
- Clarified privacy, Inventory, Distribution, Import, Pool and Activity copy without changing behavior.
- Preserved the VM-018 distinction between expected rollback protection and unexpected technical failure.

## 0.8.1-alpha - 2026-07-16 — The Activity Clarity Patch

### Fixed
- Prevented an intentionally blocked import rollback from also creating a misleading red `admin.action_failed` Activity entry.
- Represented assigned-code rollback protection as an explicit domain outcome while preserving red Activity for unexpected technical failures.
- Added VM-018 regression coverage, documentation and ADR 0027.

## 0.8.0-alpha - 2026-07-16 — The Hardened Lifecycle

### Added
- Sprint 8 Part 5.4 final Distribution Safety Review and lifecycle invariant gate.
- Keeper concurrency smoke-test matrix covering double-click, spam-click, last-voucher and multi-tab behavior.
- ADR 0026 defining the complete Distribution safety invariants after VM-014 and VM-015.
- Sprint 8 Part 5.2 one-use Distribution intent idempotency.
- Owner-scoped, ten-minute opaque intent tokens with affected-row replay protection.
- Bounded stale-intent cleanup and uninstall cleanup for runtime intent options.
- Distribution Intent Idempotency regression coverage, documentation and ADR 0024.
- Sprint 8 Part 5.1 Distribution Claim Outcome hardening.
- Nullable remaining-inventory state for post-claim metadata failures.
- Failure-contained post-claim Activity and inventory refresh boundaries.
- Distribution Claim Outcome regression coverage, documentation and ADR 0023.
- Sprint 8 Part 4 consent-aware uninstall data boundary.
- Preserved-data default with exact site-scoped cleanup for explicit destructive opt-in.
- Cron cleanup during every uninstall and strict separation from data-preserving deactivation.
- Uninstall Boundary regression coverage, documentation and ADR 0022.
- Sprint 8 Part 3 bounded Operational Activity retention through daily WordPress Cron.
- Idempotent schedule reconciliation on activation, plugin boot and Settings save.
- Data-preserving deactivation cleanup for the Activity Cron hook.
- UTC cutoff and maximum 500-row oldest-first deletion limited exclusively to `vm_logs`.
- Activity Retention regression coverage, documentation and ADR 0021.
- Sprint 8 Part 2 minimal Settings foundation for Activity retention and opt-in uninstall deletion.
- Single normalized, non-autoloaded `voucher_manager_settings` option with safe defaults.
- Explicit OFF-to-ON confirmation for destructive uninstall consent.
- Settings Foundation regression coverage, documentation and ADR 0020.

### Fixed
- Prevented original and replay redirects from racing to consume the same one-time Distribution result token.
- Give each racing replay an independent short-lived delivery token for the same already claimed voucher.
- Prevented rapid replay requests from overwriting and hiding a successfully assigned voucher result.
- Replaced the shared per-user Distribution transient with unique owner-scoped, consume-once result tokens.
- Added replay recovery, multi-tab result isolation and protected direct presentation when result persistence fails.
- Preselect the originating distributable Pool when opening Distribution from a Pool card while retaining manual selection and safe fallback behavior.
- Prevented a WordPress bootstrap fatal by separating the zero-argument `plugins_loaded` callback from the typed Activity retention reconciliation method.

## 0.7.0-alpha - 2026-07-15 — The Visible Inventory

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
