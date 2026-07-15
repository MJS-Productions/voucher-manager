# Uninstall Data Boundary

Sprint 8 Part 4 activates the explicit uninstall preference introduced in Settings.

## Default behavior

When `Delete all Voucher Manager data when the plugin is uninstalled` is OFF:

- all Pools remain;
- all Imports remain;
- all Codes remain;
- all Activity remains;
- `voucher_manager_settings` remains;
- the Activity cleanup Cron hook is removed;
- runtime plugin and database version options are removed.

Reinstalling Voucher Manager can therefore reconnect to the preserved site data and settings.

## Destructive opt-in

When the setting is ON before uninstall:

1. consent is read and normalized before any option is deleted;
2. the Activity cleanup Cron hook is removed;
3. exactly these site-prefixed tables are dropped:
   - `vm_logs`
   - `vm_codes`
   - `vm_imports`
   - `vm_pools`
4. exactly these options are removed:
   - `voucher_manager_settings`
   - `voucher_manager_version`
   - `voucher_manager_database_version`

No other WordPress table or option is targeted.

## Deactivation

Deactivation is not uninstall.

Deactivation only removes the scheduled Activity cleanup hook. It does not delete tables, options, Pools, Imports, Codes or Activity.

## Multisite

The implementation is site-scoped and uses the active site's `$wpdb->prefix`.

It does not iterate sites and does not perform network-wide deletion. Network activation and network uninstall require a separate design and test boundary.

## Database

No schema migration is introduced. Database version remains `2`.
