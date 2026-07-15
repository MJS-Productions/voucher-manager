# ADR 0025: Unique One-time Distribution Results

## Status

Accepted for Sprint 8 Part 5.3.

## Context

The previous per-user transient allowed a replay rejection or a second tab to overwrite a successfully claimed voucher result. A claimed voucher could therefore become invisible even though the intent idempotency boundary correctly prevented a second claim.

## Decision

- Replace the shared per-user transient with unique opaque result tokens.
- Store each result separately and scope it to the current administrator.
- Redirect with only the opaque result token.
- Consume each result once through an affected-row checked delete.
- Keep a short-lived intent-to-result mapping for replay recovery.
- On replay, wait briefly for the first request and redirect to its successful result when available.
- Never write a replay failure over a successful result.
- Isolate multiple tabs through independent result tokens.
- If result persistence fails after a successful claim, render the voucher directly in the protected POST response.
- Remove all ephemeral result state during uninstall.
- Keep database schema version 2.

## Consequences

A double-click can no longer hide the claimed voucher, multiple tabs cannot overwrite one another, and result-store failure cannot make a committed claim unrecoverable.
