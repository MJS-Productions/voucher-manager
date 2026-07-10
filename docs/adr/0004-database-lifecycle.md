# ADR 0004: Database lifecycle

- Status: Accepted
- Date: 2026-07-10

## Context

Voucher Manager needs reliable storage for pools, unique codes, and auditable events. Future releases must be able to evolve the schema without manual database work.

## Decision

Use dedicated WordPress-prefixed tables and a versioned migration runner based on WordPress `dbDelta()`. Run migrations during activation and when the stored schema version is older than the code's required schema version.

Plugin uninstall preserves voucher tables by default. Removing valuable codes merely because a plugin was deleted would be unsafe. A future explicit data-removal setting may opt into destructive cleanup.

## Consequences

- Installation and upgrades require no manual SQL.
- Multiple sites in a multisite network keep their own prefixed tables.
- Schema changes must increment `VOUCHER_MANAGER_DATABASE_VERSION`.
- Uninstalling the plugin does not silently destroy voucher inventory.
