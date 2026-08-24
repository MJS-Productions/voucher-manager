# Sprint 7 Release Readiness

## Candidate

Sprint 7 selected `0.7.0-alpha` — The Visible Inventory.

The milestone adds a coherent Pool Inventory administration surface without changing database schema version 2.

## Release boundary

The candidate includes:

- privacy-safe pool-scoped Inventory;
- masked administrative code references;
- public Available and Assigned filters;
- pool-scoped import filters;
- bounded 50-row pagination and result ranges;
- contextual Inventory empty states;
- conditional Reset filters behavior;
- responsive Inventory table scrolling;
- hidden Inventory detail navigation with Voucher Manager -> Pools context;
- sanitized import filename provenance;
- centralized UTC-to-WordPress-local timestamp presentation;
- read-only lifecycle-integrity attention states;
- Activity filter reset consistency.

The candidate excludes:

- full voucher reveal from Inventory;
- Inventory copy or export;
- manual code-state changes;
- bulk Inventory actions;
- automatic lifecycle repair;
- reservation, expiration or cancellation workflows;
- database migration.

## Privacy gate

Release validation must confirm:

1. Inventory repository does not hydrate the complete voucher value.
2. The read model contains only a safe suffix and sanitized provenance.
3. Short or unavailable suffixes fall back to an internal code reference.
4. Inventory exposes no Copy, Reveal or Export action.
5. Lifecycle contradictions remain read-only.
6. No automatic repair or write action is introduced.

## Navigation gate

Release validation must confirm:

1. `View inventory` remains available from Pool cards.
2. Inventory stays registered under Voucher Manager for WordPress capability checks.
3. The Inventory submenu entry is hidden visually, not removed from registration.
4. Voucher Manager remains expanded.
5. Pools remains highlighted.
6. `manage_options` remains required.

## Filter and pagination gate

Release validation must confirm:

1. only Available and Assigned are public state filters;
2. invalid states fall back to the public all-state scope;
3. import filters are validated against the selected pool;
4. filters persist across pagination;
5. page requests are bounded;
6. visible result ranges are shown;
7. Reset filters appears only with an active filter;
8. filtered empty states prioritize contextual recovery.

## Provenance and lifecycle gate

Release validation must confirm:

1. import provenance uses a pool-scoped LEFT JOIN;
2. missing import relations do not hide code records;
3. filenames are sanitized;
4. available without assignment time is healthy and shown as `Not assigned`;
5. assigned with assignment time is healthy;
6. assigned without assignment time is attention;
7. available with assignment time is attention;
8. invalid timestamps are attention;
9. contradictory data is not automatically changed.

## Automated gate

Run:

`composer quality`

Expected release identity:

`0.7.0-alpha`

Expected database schema version:

`2`

The Quality Gate must build and validate `dist/mjs-productions-voucher-manager.zip`.

## GitHub gate

All supported PHP jobs must pass:

- PHP 8.1
- PHP 8.2
- PHP 8.3
- PHP 8.4

Use the GitHub Actions `mjs-productions-voucher-manager.zip` artifact for the WordPress release-candidate test.

## WordPress smoke-test matrix

### Inventory entry and navigation

- Open Pools.
- Select `View inventory`.
- Confirm the correct pool opens.
- Confirm Voucher Manager remains expanded.
- Confirm Pools remains highlighted.
- Confirm Inventory is not a visible standalone submenu item.

### Privacy

- Confirm references remain masked.
- Confirm no complete voucher value is visible.
- Confirm no Copy, Reveal or Export action exists on Inventory.

### Filters

- Confirm default view has no Reset filters action.
- Filter Available and confirm Reset filters appears.
- Filter Assigned and confirm Reset filters appears.
- Filter by import and confirm Reset filters appears.
- Combine state and import filters.
- Reset and confirm the complete Inventory returns.

### Pagination

With more than 50 matching records:

- open page 2;
- confirm `Showing 51–100 of ... matching records`;
- confirm active filters remain selected;
- confirm pagination remains pool-scoped.

### Empty states

- empty pool -> Import Codes guidance;
- empty state filter -> contextual state message and Reset filters;
- empty import filter -> import-filter message and Reset filters.

### Provenance

- confirm `Import #ID — filename` for healthy provenance;
- confirm import filename is sanitized and no path is shown;
- confirm import and assignment times use the WordPress site display format.

### Lifecycle visibility

- Available records show `Not assigned`.
- Assigned records show an assignment time.
- If contradictory test data is deliberately prepared, confirm Attention guidance appears and no automatic change occurs.

### Activity consistency

- Activity with default filters -> no Reset filters.
- Activate Area or Outcome -> Reset filters appears.
- Reset -> complete Activity history returns.

## Final release gate

Before publication:

1. Confirm WordPress reports `0.7.0-alpha`.
2. Confirm the root release note is `RELEASE-0.7.0-alpha.md`.
3. Confirm `RELEASE-0.6.0-alpha.md` is absent from the repository root.
4. Confirm `composer quality` is green.
5. Confirm PHP 8.1–8.4 are green in GitHub Actions.
6. Install the validated GitHub Actions artifact.
7. Complete the WordPress smoke-test matrix.
8. Publish only after Keeper approval.
