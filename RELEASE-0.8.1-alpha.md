# Voucher Manager 0.8.1-alpha — The Activity Clarity Patch

This maintenance release corrects Activity semantics for protected import rollback.

## Fix

When an import already contains assigned codes, rollback is intentionally blocked to preserve those codes.

The previous implementation represented that expected business rule with an exception. Activity therefore recorded both:

- `import.rollback_blocked` as a yellow attention event;
- `admin.action_failed` as a misleading red technical error.

The protected rollback now returns an explicit blocked outcome and records only `import.rollback_blocked`.

Unexpected repository or infrastructure exceptions still pass through the administrative error boundary and remain visible as `admin.action_failed`.

## Safety boundary

This release does not change:

- the assigned-code rollback protection;
- scoped available-code deletion;
- import status transitions;
- capability, nonce or confirmation checks;
- database schema.

## Upgrade boundary

No database schema migration is introduced. `VOUCHER_MANAGER_DATABASE_VERSION` remains `2`.

## Validation

The candidate must pass:

- the complete Composer Quality Gate;
- PHP 8.1 through 8.4 in GitHub Actions;
- release-artifact validation;
- a WordPress rollback smoke test with both an allowed and a blocked rollback.
