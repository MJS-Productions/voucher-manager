# One-time Distribution Result Delivery

Sprint 8 Part 5.3 removes the shared per-user result transient.

## Unique result token

Every Distribution attempt receives a unique opaque result token after the service returns.

The stored result contains:

- owner user ID;
- short expiry;
- success state;
- voucher code for the one-time result;
- message;
- nullable remaining inventory;
- Pool ID.

The redirect URL contains only the opaque result token, never the voucher.

## Consume once

The result is owner-scoped and consumed through an affected-row checked delete.

A result token can be displayed only once. Refresh does not redistribute a voucher and does not reveal the result again.

## Replay recovery

The result store keeps a short-lived owner-scoped mapping from the original Distribution intent to its result token.

When a double-click request arrives after the intent was consumed, the controller waits briefly for the first request to finish storing its result and redirects the replay to the same one-time result.

The replay does not:

- claim another voucher;
- write a failure result;
- overwrite the successful result.

## Multi-tab isolation

Two deliberate Distribution forms have different intents and different result tokens.

Their vouchers cannot overwrite one another.

## Persistence failure

A successful claim cannot depend solely on result-store persistence.

If the one-time result cannot be stored, the authenticated POST response directly renders a protected result page. The voucher is not added to a URL, Activity or error context.

## Runtime cleanup

Distribution intent, result and intent-result mapping options are runtime-only and are removed during uninstall regardless of the business-data preservation setting.

No database migration is introduced.
