# Voucher Manager 0.5.0-alpha — The Stable Foundation

Sprint 5 turns the lessons from the first distribution releases into automated
quality controls and hardened domain rules.

## Highlights

- Quality Gate on PHP 8.1, 8.2, 8.3 and 8.4.
- Plugin structure, header and version consistency validation.
- Autoload smoke testing for critical classes.
- Automated Golden Path covering pool, import, distribution, logging and rollback protection.
- Central code-state model with explicit transition integrity.
- Error boundaries around critical WordPress admin operations.
- Privacy-aware operational logging with stable event names and severity levels.
- Validated WordPress release ZIP built by GitHub Actions.

## Operational logging

Voucher Manager records operational events rather than user profiles. Sensitive
context such as voucher values, email addresses, IP addresses, user agents,
passwords, tokens and secrets is excluded from operational logs.

## Release validation

Before publishing the GitHub Release, use the `voucher-manager.zip` artifact
produced by the successful PHP 8.4 Quality job for the WordPress smoke test.

Smoke-test checklist:

1. WordPress recognizes the package as an update of Voucher Manager.
2. Plugin activates without a fatal error.
3. Existing pools remain available.
4. Existing codes remain available.
5. TXT/CSV import works.
6. Manual distribution works.
7. Distribution logging is present.
8. No unexpected PHP warnings or fatal errors are observed.

## Known scope

`reserved`, `expired` and `cancelled` are defined domain states but are not yet
exposed as user-facing workflows.
