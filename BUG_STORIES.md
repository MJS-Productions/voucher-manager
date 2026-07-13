# Bug Stories

Because every great project has a few stories.

Some are planned.

Most are not.

## VM-001 - The Phantom Update

**Version:** 0.4.0-alpha  
**Status:** Resolved

WordPress installed the first 0.4.0 package beside the existing plugin.

**Lesson:** Plugin identity and ZIP structure are part of the product.

## VM-002 - The Missing Composer Passenger

**Version:** 0.4.0-alpha  
**Status:** Resolved

The update package expected `vendor/autoload.php`, but the release ZIP did not contain Composer dependencies.

**Lesson:** A release artifact must be self-contained and tested as an artifact, not only as source code.


## VM-004 - The Quality Gate

**Version:** 0.4.1-alpha

**Status:** In progress

The project introduced additional release discipline after the first distribution release.

Lesson:
A stable product needs repeatable release processes.


---

## VM-007 — The Test That Remembered the Old Name

**Status:** Fixed  
**Discovered in:** Sprint 5 Part 5

### Story

Operational logging moved from the legacy event names `code_distributed` and
`distribution_empty` to the stable vocabulary `distribution.completed` and
`distribution.empty`.

The application moved on. The Golden Path test did not.

The Quality Gate stopped every supported PHP job with:

`Golden path assertion failed: Each successful distribution must be logged.`

### Root cause

The production service used the new operational event vocabulary while the
integration test still asserted the legacy event names.

### Fix

The Golden Path expectations were updated to the stable operational event names.

### Lesson

When a machine-readable vocabulary changes, application code and tests must move
together. A green syntax check cannot detect semantic drift; an integration test can.

> The application moved on. The test did not.
