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

---

## VM-008 — The Danger Zone Side Door

**Status:** Fixed  
**Discovered in:** Sprint 6 Part 2.1 WordPress smoke test

### Story

The Danger Zone correctly used a dedicated confirmation flow for full pool deletion, but the narrower `Delete available codes` button submitted the destructive POST immediately. Nonce and capability checks were present, yet the approved proportional confirmation step was missing.

### Root cause

The lifecycle integrity test verified POST, nonce, capability and full-deletion confirmation, but did not assert that available-code deletion first passed through its own confirmation view.

### Fix

The Danger Zone now links to a dedicated available-code deletion confirmation page. The administrator must explicitly acknowledge the displayed affected count, and the admin boundary rejects an unconfirmed POST. Regression assertions protect both layers.

### Lesson

Technical request security does not replace deliberate destructive-action UX. A nonce proves intent to submit a form; it does not prove the administrator was shown the consequences first.


---

## VM-009 — The Activity That Forgot Its Name

**Status:** Fixed  
**Discovered in:** Sprint 6 Part 3 WordPress visual review

### Story

Pool lifecycle operations were logged with stable event names, but the Dashboard view model still knew only the older import and distribution vocabulary. Recent Activity therefore rendered several valid lifecycle events as the generic fallback `Voucher Manager activity`.

### Root cause

The lifecycle event vocabulary and its integrity tests were extended in Sprint 6 Part 2.1, while the earlier Dashboard presentation mapping and Dashboard Experience test were not extended with the same events.

### Fix

The Dashboard now presents human-readable labels and appropriate tones for available-code deletion, pool deletion and failed pool deletion. Available-code deletion includes the privacy-safe affected count, completed imports show a concise result summary, and regression assertions cover the lifecycle mappings and context-aware rendering boundary.

### Lesson

A stable event vocabulary is only operationally useful when every consuming presentation layer moves with it. Unknown-event fallbacks are safety nets, not finished user experiences.

## VM-010 — The Dots That Disappeared

The Dashboard view model contained the correct human-readable mappings, but `DashboardData` passed stored event names through WordPress `sanitize_key()`. That sanitizer removes dots, turning `pool.deleted` into `pooldeleted` and forcing every dotted operational event into the generic fallback label.

The fix preserves the stable dotted vocabulary with `sanitize_text_field()` and adds a regression assertion for the data-loading boundary.

Lesson:

> A correct mapping cannot recognize a name that was changed before it arrived.


---

## VM-012 — The Inventory That Left the Menu

**Status:** Fixed  
**Discovered in:** Sprint 7 Part 2 WordPress smoke test

### Story

Pool Inventory was correctly registered as a hidden WordPress administration detail page. When an administrator opened it through `View inventory`, WordPress no longer knew that the screen belonged to Voucher Manager → Pools. The parent menu collapsed and the current section lost its highlight.

### Root cause

`add_submenu_page( null, ... )` intentionally hid the Inventory page, but no `parent_file` or `submenu_file` mapping restored its logical menu context.

### Fix

The Inventory page now remains registered under the real Voucher Manager parent so WordPress access checks succeed. Its submenu link is hidden visually instead of being removed from the registered menu structure, while the Pools highlight remains controlled through the standard menu filters.

### Lesson

A hidden detail page still needs a visible home in the administration information architecture.


---

## VM-013 — The Empty String That Crashed Cron

**Status:** Fixed  
**Discovered in:** Sprint 8 Part 3 WordPress smoke test

### Story

The Activity retention scheduler registered its strictly typed internal `reconcile(?Settings $settings)` method directly on the WordPress `plugins_loaded` action. WordPress actions without explicit arguments may invoke callbacks with an empty string. The typed method therefore received `''` and caused a fatal `TypeError` during WordPress bootstrap.

### Fix

A zero-argument `reconcile_from_wordpress()` bridge now owns the WordPress hook boundary and calls the typed internal `reconcile()` method without passing WordPress hook arguments.

### Lesson

Strictly typed application methods should not be registered directly as WordPress callbacks unless their signatures exactly match WordPress callback behavior.
