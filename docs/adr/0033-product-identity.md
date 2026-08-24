# ADR 0033: Product Identity

## Status

Updated following WordPress.org plugin review.

## Decision

The official product name is:

**MJS-Productions Voucher Manager**

The official tagline is:

**Professional One-Time Code Management for WordPress**

The product family is:

- **MJS-Productions Voucher Manager** — Free edition
- **MJS-Productions Voucher Manager Pro** — commercial extension

The WordPress.org identity is:

- plugin slug: `mjs-productions-voucher-manager`
- text domain: `mjs-productions-voucher-manager`

The existing internal technical identity remains stable:

- PHP namespace: `VoucherManager`
- repository name: `voucher-manager`
- runtime constants, hooks, capabilities, options and database identifiers retain their established `VOUCHER_MANAGER_*` / `voucher_manager_*` forms.

## Rationale

The original name `Voucher Manager` was considered too generic during WordPress.org plugin review. Prefixing the established MJS-Productions brand creates a distinctive public plugin identity while preserving the familiar Voucher Manager product name.

The WordPress.org slug and text domain follow the new public identity. Internal runtime contracts remain unchanged because they are independent of the WordPress.org directory slug and are already used by the plugin and its extension API.

## Consequences

Public plugin identity and WordPress.org-facing metadata use **MJS-Productions Voucher Manager** and `mjs-productions-voucher-manager`.

Internal PHP namespaces, hooks, capabilities, constants, options, database identifiers, admin page slugs and the GitHub repository name remain unchanged unless a separate compatibility decision explicitly requires otherwise.
