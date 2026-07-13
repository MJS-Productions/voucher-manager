# Operational Logging

Voucher Manager records operational events, not user profiles.

## Event format

Each event has:

- a stable event name;
- a severity (`info`, `warning`, or `error`);
- a short human-readable message;
- sanitized scalar context;
- a UTC timestamp added by the WordPress repository.

Example:

```text
event: distribution.completed
level: info
pool_id: 12
code_id: 99
remaining: 8
source: manual
```

The actual distributed code is intentionally absent.

## Error boundaries

`ErrorBoundary` is used at administrative entry points. Unexpected exceptions
are translated into a safe fallback result and a privacy-aware operational log.

The boundary does not store exception messages or stack traces because they may
contain file paths, uploaded filenames or other sensitive runtime details.

## Test

Run:

```bash
composer test:operational-logging
```

The test verifies:

- sensitive fields are removed;
- scalar diagnostic fields remain;
- exception messages are not persisted;
- safe fallbacks are returned;
- a failed log write does not escape.
