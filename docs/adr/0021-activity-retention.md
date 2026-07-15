# ADR 0021: Bounded Activity Retention

## Status

Accepted for Sprint 8 Part 3.

## Context

Operational Activity is append-only and previously had no retention boundary. Synchronous or unbounded deletion could create heavy database requests, while retention must never affect Pools, Imports or Codes.

## Decision

- Use the daily WordPress Cron hook `voucher_manager_cleanup_activity`.
- Schedule it idempotently for finite retention settings.
- Remove it for `Keep indefinitely`.
- Reconcile scheduling on activation, plugin boot and Settings save.
- Clear the hook on deactivation without deleting data.
- Calculate cutoffs in UTC.
- Delete at most 500 oldest eligible rows per run.
- Restrict persistence to `vm_logs`.
- Do not log successful routine cleanup into Activity.
- Contain failures with exception-class-only PHP error logging.
- Keep database schema version 2.

## Consequences

Activity converges toward the selected retention window without creating large synchronous deletes or weakening the business-data boundary.
