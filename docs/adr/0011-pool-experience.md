# ADR 0011: Pool experience

- Status: Accepted
- Date: 2026-07-13

## Context

The pool administration page exposed domain data as a compact table. Users had
to infer whether a pool was ready for distribution, low on codes, empty or
paused.

## Decision

- Pools are presented as inventory cards.
- Each card shows available, distributed and total code counts.
- Pool state is derived from active status, available inventory and the warning threshold.
- The states are `ready`, `low`, `empty` and `inactive`.
- The primary action adapts to the pool state: distribute when ready, otherwise import.
- Delete is only offered for empty pools, matching the existing domain constraint.
- Inventory aggregation is performed outside the template.

## Consequences

The pool overview answers three questions without opening a pool: what is this
pool, how much inventory remains, and what should I do next?
