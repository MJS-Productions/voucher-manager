# Voucher Manager 0.9.3-alpha — The German Experience

Sprint 9 Part 3.3 delivers the first complete German administration experience.

## Highlights

- Bundle the complete `de_DE` translation.
- Include the editable PO catalog and compiled MO runtime catalog.
- Maintain the POT source catalog with all reviewed runtime strings.
- Use the approved terminology:
  - `One-Time Code` → `Einmalcode`
  - `Inventory` → `Bestand`
  - `Distribution` → `Ausgabe`
  - `Activity History` → `Aktivitätshistorie`
  - `Available` → `Verfügbar`
  - `Assigned` → `Ausgegeben`
  - `Danger Zone` → `Gefahrenbereich`
  - `Permanent deletion` → `Dauerhafte Löschung`
- Preserve context, placeholder and plural rules from Part 3.2.
- Preserve VM-018 rollback Activity semantics and all Distribution safety boundaries.

## Catalog files

- `languages/voucher-manager.pot`
- `languages/voucher-manager-de_DE.po`
- `languages/voucher-manager-de_DE.mo`

German WordPress installations load the bundled catalog through the existing `load_plugin_textdomain()` hook.

## English source style

The simple destructive summary does not use the serial comma:

`This permanently deletes the pool, %1$s and %2$s.`

## Upgrade boundary

No database schema migration is introduced. `VOUCHER_MANAGER_DATABASE_VERSION` remains `2`.

## Validation

The candidate must pass:

- the complete Composer Quality Gate;
- Product Language coverage;
- VM-018 Rollback Activity cleanup coverage;
- Internationalization and Translation Readiness coverage;
- German Translation Experience coverage;
- PHP 8.1 through 8.4 in GitHub Actions;
- release-artifact validation;
- a full German WordPress administration walkthrough.
