# ADR 0010: Dashboard experience

- Status: Accepted
- Date: 2026-07-13

## Context

The WordPress menu displayed `Voucher Manager` twice and the start page focused
on technical counters rather than the user's primary workflow.

## Decision

- The first submenu item is explicitly named `Dashboard`.
- The dashboard prioritizes available codes, distributed codes, pools and imports.
- Primary actions link directly to pool creation, import and distribution.
- Recent operational activity is shown using human-readable labels.
- Voucher values and personal data are never displayed in activity summaries.
- System status remains available as a secondary panel.

## Consequences

- The navigation hierarchy is understandable at a glance.
- Users can start the three primary tasks directly from the dashboard.
- Operational logging becomes useful without exposing raw technical data.
- The dashboard remains compatible with future event types through a safe fallback label.
