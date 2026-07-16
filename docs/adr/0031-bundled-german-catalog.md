# ADR 0031: Bundle the Complete German Catalog

## Status

Accepted for Sprint 9 Part 3.3.

## Context

The English source language, text-domain boundary, translator context, placeholder guidance and plural rules are complete. Free 1.0 requires a professional German administration experience.

## Decision

Bundle a complete `de_DE` translation in PO and MO format and maintain the POT source catalog in the repository.

Use the approved German Product Language contract:

- `One-Time Code` → `Einmalcode`
- `Inventory` → `Bestand`
- `Distribution` → `Ausgabe`
- `Activity History` → `Aktivitätshistorie`
- `Assigned` → `Ausgegeben`
- `Available` → `Verfügbar`
- `Permanent deletion` → `Dauerhafte Löschung`

Protect catalog completeness, placeholder parity, glossary terms and MO readability with a pre-build regression gate.

## Consequences

German WordPress installations receive the bundled interface automatically. Future source-string changes must update POT, PO and MO together.
