# Operational Visibility

Sprint 6 Part 5 adds a dedicated **Voucher Manager → Activity** screen.

## What administrators can see

- complete paginated operational history;
- human-readable event labels;
- success, attention and error severity;
- safe identifiers such as pool or import IDs;
- inventory and affected-row counts where available;
- guidance for common warnings and failures.

## Filters

Activity can be filtered by area:

- Imports
- Distribution
- Pools
- Administration

It can also be filtered by outcome:

- Success
- Attention
- Errors

All filter values are allowlisted before they are used by the data provider.

## Privacy boundary

The screen deliberately does not render raw database log messages or raw JSON context.

It never presents:

- voucher values;
- email addresses;
- IP addresses;
- user agents;
- passwords, tokens or secrets;
- exception messages;
- stack traces.

Technical debugging details remain in the normal WordPress/PHP error log.

## Dashboard integration

The dashboard continues to show the five newest events and now links to the complete Activity history.

## Quality protection

`tests/Integration/OperationalVisibilityTest.php` protects filtering, pagination, administrator access, actionable guidance and the no-raw-context presentation boundary.
