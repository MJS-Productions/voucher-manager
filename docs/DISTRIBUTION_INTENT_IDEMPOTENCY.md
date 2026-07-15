# Distribution Intent Idempotency

Sprint 8 Part 5.2 protects manual Distribution from accidental replay.

## One-use intent

Every rendered Distribution form receives a unique opaque token.

The token:

- is generated from 32 cryptographically random bytes;
- is never a voucher value;
- is stored only in a hashed option name;
- is scoped to the current administrator;
- expires after 10 minutes;
- is stored in a non-autoloaded option.

## Consumption order

POST processing keeps the existing capability and nonce checks.

After those checks, the one-use intent is consumed before `DistributionService::distribute()` is called.

A repeated, expired, malformed or foreign-user token returns:

`This distribution request has already been used or expired. Reload Distribution and try again.`

No voucher claim occurs for a rejected intent.

## Atomic replay boundary

Consumption removes the exact option name and option value through one affected-row database delete.

Only the request that deletes one row succeeds. A parallel replay sees zero affected rows and cannot claim another voucher from the same intent.

## Multiple deliberate actions

Two separately rendered forms receive different tokens.

Therefore:

- replaying Intent A is blocked;
- Intent B remains valid;
- intentionally performing two distinct Distribution actions remains possible.

## Expired intent cleanup

Creating a new intent opportunistically inspects at most 25 oldest intent options and removes malformed or expired entries.

Uninstall always removes all runtime-only Distribution intent options, even when business data is preserved.

## Scope

Part 5.2 does not change one-time voucher result delivery. The current result transient is hardened separately in Part 5.3.

No database migration is introduced.
