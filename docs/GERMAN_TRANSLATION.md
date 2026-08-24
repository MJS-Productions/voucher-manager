# German Translation (`de_DE`)

## Status

Complete for Sprint 9 Part 3.3.

## Catalogs

- `languages/voucher-manager.pot` — source catalog
- `languages/voucher-manager-de_DE.po` — editable German catalog
- `languages/voucher-manager-de_DE.mo` — deterministically compiled repository verification artifact

## Approved terminology

| English | German |
| --- | --- |
| One-Time Code | Einmalcode |
| One-Time Codes | Einmalcodes |
| Pool | Pool |
| Inventory | Bestand |
| Distribution | Ausgabe |
| Activity | Aktivität |
| Activity History | Aktivitätshistorie |
| Available | Verfügbar |
| Assigned | Ausgegeben |
| Import | Import |
| Settings | Einstellungen |
| Danger Zone | Gefahrenbereich |
| Permanent deletion | Dauerhafte Löschung |
| Import record | Importeintrag |
| Reference | Referenz |
| Remaining | Verbleibend |
| Rollback | Rollback |

## Tone

German copy is neutral-professional. Direct `du` or formal `Sie` address is avoided where an impersonal instruction is natural.

The standard WordPress permission message remains aligned with the German WordPress wording.

## English style

The source language does not use the serial comma in simple three-part lists.

Approved:

`This permanently deletes the pool, %1$s and %2$s.`

## Translation delivery

The repository maintains POT, PO and deterministically compiled MO artifacts for translation review and integrity testing.

The WordPress.org release package does not bundle PO or MO catalogs and does not call `load_plugin_textdomain()`. Production translations are delivered through WordPress language packs.

## Boundaries

Technical identifiers, event keys, stored status values, database names and exception diagnostics remain untranslated.


## Language polish revision

The German catalog was reviewed in WordPress and polished for natural administrator-facing language.

Notable decisions:

- `in a future Pro extension` → `in the Pro extension`
- `Import zurückrollen` → `Import rückgängig machen`
- `Import zurückgerollt` → `Import erfolgreich rückgängig gemacht`
- technical `atomar` wording removed from the Distribution guidance
- `Operational Activity retention` → `Aufbewahrungsdauer der Aktivitätshistorie`
- `Uninstall data boundary` → `Verhalten bei der Deinstallation`
- `business data` → `Daten von Voucher Manager`


## Context review revision

A WordPress page-by-page review refined the German experience:

- Dashboard: `Bestand verwalten`
- Dashboard Activity: `Die letzten Aktivitäten`
- Activity introduction: `Aktivitäten prüfen und wichtige Vorgänge schnell erkennen`
- Import rollback: all codes from the import are removed or none are removed
- Distribution result: the redundant completion eyebrow was removed
- Indefinite Activity retention: only the permanent-retention explanation remains visible
