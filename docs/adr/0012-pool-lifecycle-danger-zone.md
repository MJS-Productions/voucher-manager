# ADR 0012: Pool lifecycle and Danger Zone

## Status
Accepted for Sprint 6 Part 2.1.

## Decision
Destructive pool lifecycle operations are centralized in `PoolLifecycleService` and a lifecycle persistence abstraction. The WordPress repository owns the database transaction for full deletion and deletes codes, imports, then the pool before commit. Any failure rolls the transaction back.

The Pool overview always exposes a secondary Danger Zone path. Destructive execution is POST-only at the admin boundary, protected by `manage_options` and WordPress nonces. Full deletion additionally requires the exact pool name.

Deleting available codes removes only `available` rows. Full deletion removes all pool codes and import records. Operational logs remain independent historical records and may retain numeric `pool_id` context. Voucher values are never added to lifecycle log context.

Stable events are `pool.available_codes_deleted`, `pool.deleted`, and `pool.delete_failed`. Success logging occurs after the destructive repository operation has committed; logging failure cannot invalidate the committed lifecycle operation.
