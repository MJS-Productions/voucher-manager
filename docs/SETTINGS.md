# Voucher Manager Settings

Sprint 8 Part 2 introduces a deliberately small Settings surface.

## Settings option

All user-facing settings are stored in one non-autoloaded WordPress option:

`voucher_manager_settings`

Normalized structure:

```php
array(
    'activity_retention_days'  => 90,
    'delete_data_on_uninstall' => false,
)
```

Missing or invalid values fall back safely.

## Operational Activity retention

Available preferences:

- 30 days
- 90 days
- 180 days
- Keep indefinitely

Default:

`90 days`

Sprint 8 Part 3 applies this preference through bounded daily WordPress Cron cleanup. Saving the setting reconciles the schedule but does not synchronously delete Activity.

## Uninstall data boundary

Default:

`Delete all Voucher Manager data when the plugin is uninstalled` is OFF.

Enabling the setting requires explicit acknowledgement of permanent deletion. The confirmation is required for the OFF-to-ON transition, but not for ordinary later saves while the setting remains enabled.

Deactivation never deletes data.

Sprint 8 Part 4 applies this preference during uninstall. The default remains data preservation; only explicit opt-in removes all plugin-owned tables and options.

## Security

- Settings require `manage_options`.
- Saves use POST through `admin-post.php`.
- Saves require a WordPress nonce.
- Retention values are allowlisted.
- Destructive consent is never inferred.
- No database migration is introduced.
