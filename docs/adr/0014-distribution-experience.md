# ADR 0014: Distribution Experience

## Status

Accepted for Sprint 6 Part 4.

## Context

Manual distribution is the plugin's decisive operational moment. The existing screen safely claimed a code atomically, but pool choices lacked inventory context and the successful voucher value was presented as a plain code block with little guidance.

## Decision

Keep the existing atomic domain operation and improve its administration presentation.

- Prepare active-pool inventory with `PoolOverviewData`.
- Centralize distribution presentation rules in `DistributionViewModel`.
- Offer only active pools with available inventory in the manual selector.
- Present the claimed voucher as a one-time result with an explicit copy action.
- Consume the result transient after one render.
- Explain remaining inventory and guide empty inventory back to Import.
- Preserve POST, nonce, capability, atomic claim and privacy-safe operational logging behavior.

## Consequences

The domain claim semantics remain unchanged. The administration experience now makes pool inventory, result handling and next steps explicit without introducing a distribution history containing voucher values.
