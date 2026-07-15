# ADR 0020: Settings Foundation

## Status

Accepted for Sprint 8 Part 2.

## Context

Voucher Manager needs production-hardening controls for Activity retention and optional destructive uninstall. A general-purpose Settings page would invite unnecessary configuration and blur existing safety boundaries.

## Decision

Add one Settings submenu containing exactly two controls:

1. Activity retention: 30, 90, 180 days or indefinitely.
2. Delete all plugin data on uninstall, default OFF.

Persist both controls in one non-autoloaded option named `voucher_manager_settings`.

Require `manage_options`, POST and a nonce for saves.

Require explicit confirmation only when destructive uninstall changes from OFF to ON.

Do not schedule Activity cleanup and do not modify destructive uninstall behavior in Part 2.

## Consequences

The product gains a clear configuration boundary without prematurely activating either destructive lifecycle operation. Cron cleanup and uninstall execution remain separately reviewable Parts.
