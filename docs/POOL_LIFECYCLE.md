# Pool Lifecycle & Danger Zone

Sprint 6 Part 2.1 separates deactivation from permanent deletion. Deactivation pauses the existing active workflow and preserves data.

Every pool has a Danger Zone. Its confirmation page presents total, available, distributed and import counts before any destructive POST.

`Delete available codes` removes only unused `available` codes and preserves assigned codes, the pool, imports and operational logs. `Delete pool and all associated data` requires the administrator to type the exact pool name and atomically removes all codes, import records and the pool. The database operation rolls back on failure.

Lifecycle logging uses privacy-safe technical counts and IDs only. Voucher values, exception messages and stack traces are not persisted.
