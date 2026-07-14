# ADR 0019: Code Provenance and Lifecycle Visibility

## Status

Accepted for Sprint 7 Part 4.

## Decision

Inventory remains read-only and gains defensive provenance and lifecycle presentation.

- Join code records to imports with a pool-scoped LEFT JOIN.
- Keep codes visible when import provenance is missing.
- Add only sanitized import filenames to the privacy-safe read model.
- Centralize UTC-to-WordPress-local timestamp formatting in InventoryViewModel.
- Treat available without assigned_at and assigned with assigned_at as healthy.
- Mark assigned without assigned_at, available with assigned_at, or invalid timestamps as attention.
- Never repair or mutate inconsistent records automatically.
- Keep complete voucher values out of the read model and template.
- Do not change the database schema version.

## Consequences

Administrators can understand source and lifecycle facts, including contradictions, without creating a new disclosure or repair surface.
