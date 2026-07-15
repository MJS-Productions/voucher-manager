# Distribution Claim Outcome Hardening

Sprint 8 Part 5.1 makes the committed voucher claim authoritative.

## Authoritative boundary

`claim_next_available()` is the business write boundary.

Once it returns a claimed voucher, the code has already transitioned atomically from `available` to `assigned`.

The service must therefore return a successful voucher result even when later operational metadata cannot be refreshed or persisted.

## Remaining inventory

The post-claim remaining count is presentation metadata.

When the count succeeds, the exact integer is returned.

When the count fails:

- the claimed voucher remains assigned;
- the voucher is still returned as a successful result;
- `remaining` is `null`;
- the UI says that the code was assigned successfully but remaining inventory could not be refreshed.

Unknown remaining inventory is never coerced to zero.

## Operational Activity

Completed-distribution Activity is operational visibility, not the authoritative business write.

If Activity persistence fails after a claim:

- the claim remains successful;
- the voucher is returned;
- no retry or release is attempted;
- only the failure stage and exception class are written to PHP error logging.

The voucher value and exception message are not logged.

The same safe logging boundary is used for the non-destructive empty-distribution event.

## Atomic claim

Part 5.1 does not change the database claim transaction.

The repository retains:

- `START TRANSACTION`;
- oldest available row selection;
- `FOR UPDATE`;
- guarded `available → assigned` update;
- exact affected-row check;
- `COMMIT`;
- `ROLLBACK` on failure.

## Database

No schema migration is introduced. Database version remains `2`.
