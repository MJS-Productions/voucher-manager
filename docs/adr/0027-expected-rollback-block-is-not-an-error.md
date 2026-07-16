# ADR 0027: Expected Rollback Block Is Not an Administrative Error

## Status

Accepted for VM-018.

## Context

Import rollback protection intentionally refuses deletion when at least one code from the import has already been assigned. Representing this expected outcome with an exception caused the generic administrative error boundary to create a second red Activity entry.

## Decision

Represent assigned-code rollback protection as the explicit domain result `false`.

Record exactly one `import.rollback_blocked` event in the Import Service.

Reserve exceptions and `admin.action_failed` for unexpected technical failures.

## Consequences

Activity severity now matches business meaning. The rollback safety rule, deletion scope and technical failure visibility remain unchanged.
