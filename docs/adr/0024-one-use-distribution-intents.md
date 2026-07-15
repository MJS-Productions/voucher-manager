# ADR 0024: One-Use Distribution Intents

## Status

Accepted for Sprint 8 Part 5.2.

## Context

WordPress nonces prevent CSRF but are not single-use request identifiers. Double-clicks or browser POST resubmission could execute two valid Distribution claims.

## Decision

- Generate one opaque intent for every rendered Distribution form.
- Store the intent as a non-autoloaded, owner-scoped, 10-minute option.
- Hash the token in the option name.
- Consume the intent before voucher claim execution.
- Use an affected-row checked delete so one replay can succeed at most once.
- Reject malformed, expired, foreign or repeated intents without claiming a voucher.
- Allow separately rendered forms to have independently valid intents.
- Opportunistically remove a bounded number of stale intents.
- Remove all runtime intent options during uninstall.
- Keep database schema version 2.

## Consequences

Accidental replay of the same form cannot distribute another voucher, while deliberate separate Distribution actions remain supported.
