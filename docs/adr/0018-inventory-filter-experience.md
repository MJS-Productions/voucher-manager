# ADR 0018: Inventory Filter and Pagination Experience

## Status

Accepted for Sprint 7 Part 3.

## Context

The Part 2 Inventory data layer correctly supported pool scoping, state/import filters and pagination. Its presentation used one generic empty state and did not clearly distinguish pool totals from filtered results.

## Decision

Keep the repository behavior unchanged and improve centralized presentation.

- Label the metrics explicitly as pool totals.
- Summarize active state and import filters.
- Show the visible result range.
- Display Reset filters only when filters are active.
- Use contextual empty-state titles and guidance.
- Prioritize Reset filters for empty filtered results.
- Show Import Codes only when the pool is empty or has no available inventory.
- Wrap the inventory table in a responsive horizontal-scroll container.

## Consequences

Administrators can understand whether they are viewing the complete pool or a filtered subset and receive recovery actions that match the active context. No schema or query change is required.
