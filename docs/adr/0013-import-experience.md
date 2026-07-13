# ADR 0013: Import Experience

## Status
Accepted for Sprint 6 Part 3.

## Context
The import engine already streams TXT/CSV files, batches inserts, counts invalid rows and skips duplicates. The administration experience did not explain these rules before upload, presented results as dense technical counters, and exposed rollback as a destructive GET link protected primarily by a browser confirmation dialog.

## Decision
Introduce `ImportViewModel` for status, tone, result summaries and rollback availability. Enrich pool choices with inventory context. Present import rules before upload and interpret zero-result imports. Route rollback through a dedicated review page, then execute only through a capability- and nonce-protected POST with explicit acknowledgement.

Protected rollback semantics remain unchanged: if any code from an import has been assigned, the whole rollback is blocked and no codes are removed.

## Consequences
The import engine and domain behavior remain stable while the first experience becomes clearer and destructive rollback receives the same deliberate admin-boundary treatment established by the Danger Zone work.
