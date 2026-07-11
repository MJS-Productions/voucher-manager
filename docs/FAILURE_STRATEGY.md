# Failure Strategy

Voucher Manager distinguishes between recoverable domain failures and unexpected technical failures.

## Recoverable failures

These must not crash WordPress. They return a controlled result, create a privacy-conscious log entry where useful, and show an actionable admin message.

Examples:

- inactive or missing pool;
- no available codes;
- duplicate codes during import;
- invalid or empty import rows;
- rollback requested after a code was assigned;
- unsupported or unreadable import file.

## Unexpected technical failures

Database, filesystem or programming failures may be exceptional, but they must be isolated from unrelated admin screens wherever practical.

Rules:

1. Never expose stack traces, SQL statements or secret code values to visitors.
2. Never delete existing pools or codes as part of error recovery.
3. Log identifiers and operational context, not personal data or full distributed code values.
4. Admin actions require capability checks and nonces before state changes.
5. The plugin bootstrap and release artifact must pass automated smoke tests before release.
6. A failed import is marked as failed and must not be reported as completed.
7. Empty distribution is a normal business condition, not an exception.

## Release response

A failed quality-gate check blocks release. A failed manual WordPress smoke test blocks the GitHub release even when CI is green.
