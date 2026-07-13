# Distribution Experience

Sprint 6 Part 4 improves the manual `available -> assigned` workflow.

## Pool selection

Only active pools with available codes are selectable. Each option includes available and total inventory counts.

If no code is ready, the screen presents an Import Codes action instead of inviting a known-empty distribution attempt.

## One-time result

A successful distribution shows the assigned voucher prominently and provides a Copy code action. The result is held in the existing short-lived per-user transient and deleted immediately when rendered.

The voucher value is not added to operational log context or to a new distribution-history view.

Refreshing the result page does not distribute another code. A new distribution always requires a new nonce-protected POST action.

## Inventory guidance

The result explains how many codes remain. When the final code is distributed, the result changes to a warning presentation and directs the administrator to import more codes.

## Quality protection

`tests/Integration/DistributionExperienceTest.php` protects inventory-aware pool selection, one-time result presentation, the copy action, empty-inventory guidance and the existing security boundary.
