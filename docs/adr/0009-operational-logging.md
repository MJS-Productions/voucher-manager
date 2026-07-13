# ADR 0009: Error boundaries and operational logging

- Status: Accepted
- Date: 2026-07-13

## Context

Administrative actions may fail because of database, filesystem or runtime
conditions. Expected failures should become useful results, while unexpected
exceptions must not crash WordPress administration.

Operational diagnostics must remain useful without collecting unnecessary
personal data or voucher values.

## Decision

- Administrative entry points use `ErrorBoundary` around operations that may
  throw unexpectedly.
- The boundary returns a safe fallback and records a stable operational event.
- Exception messages and stack traces are not persisted by the plugin.
- `OperationalLogger` removes sensitive context keys and complex values.
- Log persistence failures are contained and may not trigger a second failure.
- Event names come from `OperationalEvent`.
- Severity comes from `LogLevel`.

## Privacy rules

Operational logs may include identifiers and counters such as:

- pool ID;
- import ID;
- internal code ID;
- remaining count;
- action source;
- exception class.

Operational logs must not include:

- voucher/code values;
- email addresses;
- IP addresses;
- user agents;
- passwords, tokens or secrets;
- arbitrary nested objects.

## Consequences

- Admin actions fail safely instead of producing avoidable fatal errors.
- Logs are machine-readable and suitable for later reporting.
- Diagnostic value is retained without creating user profiles.
- Logging itself is not allowed to take down the application.
