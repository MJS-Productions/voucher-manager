# ADR 0017: Pool Inventory

## Status

Accepted for Sprint 7 Part 2.

## Context

Voucher Manager can summarize available and assigned inventory per pool, but administrators cannot inspect the records that make up those totals. A complete voucher-value list would conflict with the established privacy boundary and one-time Distribution result.

## Decision

Add a read-only, pool-scoped Inventory screen.

- Add a secondary `View inventory` action to each Pool card.
- Use a dedicated `CodeInventoryRepository` read abstraction.
- Query only `available` and `assigned` records.
- Select only the final four characters of voucher values, and only when the stored value is longer than four characters.
- Keep the complete voucher value out of inventory DTOs and templates.
- Present masked references, internal IDs, state, import provenance, imported time and assigned time.
- Allowlist state filters to `all`, `available` and `assigned`.
- Validate import filters against imports that belong to the selected pool.
- Use 50-row offset pagination with deterministic `ORDER BY id DESC`.
- Keep the screen read-only and protected by `manage_options`.
- Do not increase the database schema version.

## Consequences

Administrators can understand pool inventory without creating a second voucher-disclosure surface. Full voucher values remain confined to the one-time successful Distribution result.
