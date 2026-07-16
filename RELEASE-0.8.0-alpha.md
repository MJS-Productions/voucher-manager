# Voucher Manager 0.8.0-alpha — The Hardened Lifecycle

Sprint 8 hardens the plugin's operational lifecycle and the complete manual Distribution path.

## Highlights

- Configure Operational Activity retention for 30, 90 or 180 days, or keep Activity indefinitely.
- Run bounded daily Activity cleanup through WordPress Cron with a maximum of 500 oldest eligible rows per run.
- Preserve all business data by default during uninstall.
- Remove all plugin-owned tables and options only after explicit destructive consent.
- Keep deactivation strictly data-preserving.
- Treat a committed One-Time Code claim as the authoritative business outcome.
- Prevent remaining-count or Activity failures from hiding an already assigned code.
- Prevent rapid duplicate submissions with owner-scoped, one-use Distribution intents.
- Deliver results through unique owner-scoped, consume-once tokens.
- Recover racing replay requests without assigning another code or overwriting the successful result.
- Preserve the assigned code through protected direct presentation if result persistence fails.
- Preselect the originating Pool when Distribution is opened from a Pool card.
- Protect the complete lifecycle with the final Distribution safety invariant gate and Keeper concurrency smoke-test matrix.

## Data and privacy boundary

Activity retention touches only `vm_logs`.

Consent-aware destructive uninstall targets exactly:

- `vm_logs`
- `vm_codes`
- `vm_imports`
- `vm_pools`
- Voucher Manager settings and runtime version options

One-Time Code values are excluded from redirect URLs, Activity context and post-claim error messages.

## Upgrade boundary

No database schema migration is introduced. `VOUCHER_MANAGER_DATABASE_VERSION` remains `2`.

Existing Pools, Imports, Codes, Activity and Settings remain compatible.

## Validation

The candidate must pass:

- the complete Composer Quality Gate;
- GitHub Actions on PHP 8.1 through 8.4;
- release-artifact validation;
- WordPress activation, deactivation and uninstall smoke tests;
- normal click, rapid double-click, spam-click, last-code and multi-tab Distribution tests.
