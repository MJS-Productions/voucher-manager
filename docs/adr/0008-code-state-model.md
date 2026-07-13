# ADR 0008: Code state model

- Status: Accepted
- Date: 2026-07-13

## Context

Voucher Manager currently distributes codes directly from `available` to `assigned`.
Future integrations may need temporary reservations, expiry handling and explicit
cancellation without allowing arbitrary status changes.

## Decision

The domain defines these states:

- `available`
- `reserved`
- `assigned`
- `expired`
- `cancelled`

All transitions must be approved by `CodeStateMachine`.

Currently exposed application workflows continue to use only:

```text
available -> assigned
```

`reserved`, `expired` and `cancelled` are domain preparation, not active user
features.

## Allowed transitions

```text
available -> reserved
available -> assigned
available -> expired
available -> cancelled

reserved -> available
reserved -> assigned
reserved -> expired
reserved -> cancelled
```

`assigned`, `expired` and `cancelled` are terminal states in the current model.

## Consequences

- Status vocabulary is centralized.
- Invalid transitions fail before persistence.
- Future integrations can use reservations without redefining lifecycle rules.
- Database migration is not required for this sprint because status values are
  stored as strings.
