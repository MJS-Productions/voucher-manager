# ADR 0033: Product Identity

## Status

Updated following WordPress.org plugin review.

## Decision

The official base-plugin name is:

**MJS-Productions Voucher Manager**

The official tagline is:

**Professional One-Time Code Management for WordPress**

The commercial extension is:

**Voucher Manager PRO**

The `MJS-Productions` prefix is part of the base plugin's public identity following WordPress.org review. It is not a naming prefix for Voucher Manager PRO.

The WordPress.org identity is:

- plugin slug: `mjs-productions-voucher-manager`
- text domain: `mjs-productions-voucher-manager`

The existing internal technical identity remains stable:

- PHP namespace: `VoucherManager`
- repository name: `voucher-manager`
- runtime constants, hooks, capabilities, options and database identifiers retain their established `VOUCHER_MANAGER_*` / `voucher_manager_*` forms.

## Rationale

The original base-plugin name `Voucher Manager` was considered too generic during WordPress.org plugin review. Prefixing the established MJS-Productions brand creates a distinctive public plugin identity while preserving the familiar Voucher Manager product name.

This WordPress.org-driven naming requirement applies to the base plugin. The commercial extension retains its official name **Voucher Manager PRO** without the MJS-Productions prefix.

The WordPress.org slug and text domain follow the base plugin's public identity. Internal runtime contracts remain unchanged because they are independent of the WordPress.org directory slug and are already used by the plugin and its extension API.

## Consequences

Public base-plugin identity and WordPress.org-facing metadata use **MJS-Productions Voucher Manager** and `mjs-productions-voucher-manager`.

The commercial extension uses **Voucher Manager PRO**.

Internal PHP namespaces, hooks, capabilities, constants, options, database identifiers, admin page slugs and the GitHub repository name remain unchanged unless a separate compatibility decision explicitly requires otherwise.
