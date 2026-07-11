# ADR 0007: Import architecture

- Status: Accepted
- Date: 2026-07-11

## Decision

Manual TXT and CSV imports are core functionality and remain available in the free edition without artificial row limits. Files are streamed and codes are inserted in bounded batches. Each import is recorded and can be rolled back while none of its codes have been assigned.

Automated sources, scheduling, remote synchronisation, and background queues may be delivered by Pro extensions.
