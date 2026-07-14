# Pool Inventory

Sprint 7 Part 2 adds a read-only Inventory screen for each pool.

## Entry point

Each Pool card includes **View inventory**. The resulting page remains scoped to that pool.

## Information shown

- masked voucher reference;
- internal code ID;
- current state;
- source import;
- imported timestamp;
- assigned timestamp.

Complete voucher values are not loaded into inventory records and are not rendered.

## Masking

For voucher values longer than four characters, the repository returns only the final four-character suffix. The UI renders it as a masked reference such as:

`••••••••7X4P`

Short values use an internal fallback such as `Code #102` so the Inventory page never reveals the entire voucher.

## Filters

User-facing state filters are limited to:

- All states
- Available
- Assigned

Prepared domain states are not exposed.

Import filters are validated against imports represented in the selected pool.

## Pagination

Inventory uses 50 rows per page and deterministic newest-first ordering by internal code ID.

## Security and privacy

- `manage_options` is required;
- the page is read-only;
- no Copy, Reveal or Export action is available;
- no raw hash or operational context is shown;
- the database schema remains at version 2.

## Quality protection

`tests/Integration/InventoryExperienceTest.php` protects pool scoping, filter allowlisting, pagination bounds, masking, template privacy and the no-migration decision.


## WordPress navigation context

Inventory is registered under Voucher Manager so WordPress retains the real parent relationship and access capability. Its submenu link is hidden visually in the admin menu rather than removed from WordPress registration.

While Inventory is open:

- the Voucher Manager parent menu remains expanded;
- Pools remains highlighted as the logical parent section;
- the Inventory page remains reachable only through pool-specific `View inventory` links.


## Filter and pagination experience

The Inventory screen distinguishes pool-wide totals from filtered results.

When filters are active, it shows a readable summary such as:

`Available · Import #12 — codes.csv`

Pagination explains the visible range:

`Showing 51–100 of 120 matching records`

Empty states are contextual:

- an empty pool guides the administrator to Import Codes;
- an empty state filter explains that no records match that state;
- an empty import filter explains that no records match the selected import;
- filtered emptiness prioritizes Reset filters rather than suggesting unrelated actions.

The Reset filters action appears only while a filter is active.

The five-column inventory table uses a horizontal-scroll container on narrow screens so timestamps and provenance are not hidden.
