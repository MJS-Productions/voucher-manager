# ADR 0016: Sprint 6 Release Hardening

## Status

Accepted for Sprint 6 Part 6.

## Context

Sprint 6 introduced multiple administration experiences and three manual-smoke-test regressions exposed cross-layer gaps: a destructive confirmation boundary was not protected, new stable events lacked presentation mappings, and dotted event names were altered before presentation.

The existing feature-specific tests correctly protected their own areas but did not explicitly assert that stable operational vocabulary and documentation boundaries remained synchronized across the complete First Experience.

## Decision

Add a final Sprint 6 hardening gate before release build.

The gate verifies:

- every `OperationalEvent` enum case has a non-fallback human-readable Dashboard label;
- dotted event vocabulary is preserved at the Dashboard data boundary;
- the Activity screen never renders raw log messages or raw context;
- destructive available-code deletion retains its dedicated confirmation and server-side acknowledgement;
- import rollback retains its dedicated review and confirmed POST boundary;
- distribution result transients are consumed after one render;
- README development/release language remains explicit;
- changelog structure contains exactly one top-level heading and one Unreleased section;
- obsolete root documentation does not return;
- Sprint 6 release-readiness documentation exists.

## Consequences

Part 6 adds no user-facing feature. It turns lessons from VM-008, VM-009 and VM-010 into cross-layer release protection and leaves version selection to the dedicated release review.
