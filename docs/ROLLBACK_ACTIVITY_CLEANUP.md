# VM-018 — Rollback Activity Cleanup

## Problem

A rollback attempt against an import containing assigned codes is an expected business-rule outcome.

The previous flow threw a `RuntimeException`. The shared administrative `ErrorBoundary` correctly treated that exception as unexpected and recorded `admin.action_failed`. The controller then also recorded `import.rollback_blocked`.

Activity therefore showed both:

- a yellow expected rollback block;
- a red administrative failure.

## Decision

`ImportService::rollback()` now returns:

- an integer for a completed rollback;
- `false` for the expected assigned-code protection;
- an exception only for unexpected repository or infrastructure failure.

The service records `import.rollback_blocked` itself before returning `false`.

The controller maps:

- `false` to the existing rollback-blocked notice;
- `null` from `ErrorBoundary` to the generic technical import error;
- an integer to the successful rollback notice.

## Activity semantics

| Outcome | Activity |
| --- | --- |
| Rollback completed | `import.rolled_back` |
| Assigned codes protect the import | `import.rollback_blocked` |
| Unexpected technical exception | `admin.action_failed` |

No database migration is introduced.
