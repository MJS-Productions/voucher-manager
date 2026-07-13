# ADR 0015: Operational Visibility

## Status

Accepted for Sprint 6 Part 5.

## Context

Voucher Manager already records privacy-safe operational events and shows the five newest events on the dashboard. Administrators still lacked a complete history, filters and clear guidance for warnings or failures.

## Decision

Add a dedicated Activity administration page.

- Register an `Activity` submenu under Voucher Manager.
- Load operational history through a centralized, paginated data provider.
- Allowlist area and outcome filters.
- Reuse the stable human-readable event vocabulary.
- Present severity, safe technical identifiers, counts and actionable guidance.
- Never render raw log messages or raw JSON context.
- Keep voucher values, personal data, exception messages and stack traces hidden.
- Link the dashboard's Recent Activity panel to the complete history.

## Consequences

Operational logs become useful for day-to-day administration without turning the plugin database into a sensitive diagnostic dump. Deep technical debugging remains in the WordPress/PHP error log.
