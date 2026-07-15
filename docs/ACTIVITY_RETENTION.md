# Operational Activity Retention

Sprint 8 Part 3 gives the Activity retention setting an asynchronous, bounded cleanup mechanism.

## Schedule

Hook:

`voucher_manager_cleanup_activity`

Cadence:

Daily.

A finite retention preference schedules the hook idempotently. `Keep indefinitely` clears the hook. Switching back to 30, 90 or 180 days schedules it again.

## Cleanup boundary

Each run:

1. reads the normalized current settings;
2. stops and unschedules when retention is indefinite;
3. calculates the cutoff in UTC;
4. deletes at most 500 oldest eligible rows from `vm_logs`.

The cleanup repository does not access:

- `vm_pools`
- `vm_imports`
- `vm_codes`

Activity cleanup never runs synchronously while rendering Activity or saving Settings.

## Lifecycle

Activation reconciles the schedule.

Normal plugin boot also reconciles it idempotently, which restores a missing event without creating duplicates.

Saving Settings immediately reconciles the schedule.

Deactivation clears the scheduled hook and preserves every table, option and business record.

Uninstall cleanup remains reserved for Sprint 8 Part 4.

## Failure boundary

Routine successful cleanup is not written back into Activity, avoiding self-generating cleanup noise.

A bounded failure writes only the exception class to the PHP error log. It does not expose SQL, voucher values or raw Activity context.

## Database

No schema migration is introduced. Database version remains `2`.


## WordPress callback boundary

The `plugins_loaded` action uses a dedicated zero-argument bridge method. WordPress may provide an empty string to callbacks for actions without explicit arguments, so the strictly typed internal reconciliation method is never registered directly as the action callback.
