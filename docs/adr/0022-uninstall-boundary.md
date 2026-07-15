# ADR 0022: Consent-Aware Uninstall Boundary

## Status

Accepted for Sprint 8 Part 4.

## Context

Voucher Manager must preserve business data by default while honoring an administrator's explicit request to remove all plugin-owned data during uninstall. Deactivation and uninstall must remain separate.

## Decision

- Read destructive consent before deleting any option.
- Always clear the Activity cleanup Cron hook.
- Preserve all four business tables and the Settings option by default.
- Remove only runtime version options in the default path.
- With explicit opt-in, drop exactly `vm_logs`, `vm_codes`, `vm_imports` and `vm_pools`.
- With explicit opt-in, delete exactly the Settings, plugin-version and database-version options.
- Keep cleanup site-scoped to `$wpdb->prefix`.
- Do not add network-wide site iteration.
- Keep deactivation data-preserving.
- Keep database schema version 2.

## Consequences

Administrators retain control over data ownership without accidental deletion. The destructive path is explicit, allowlisted and isolated from routine deactivation.
