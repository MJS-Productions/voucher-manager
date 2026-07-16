# Product Language Guide

**Status:** Approved for Sprint 9  
**Source language:** English  
**Translation style:** Neutral-professional German

## Product identity

The current public product name remains `Voucher Manager` until the separate naming decision is completed.

Technical identifiers remain unchanged:

- `VoucherManager` namespace
- `voucher-manager` text domain
- `voucher_manager_*` hooks and options
- database table names
- persisted status and event values

## Core object

Use:

- `One-Time Code`
- `One-Time Codes`

Always hyphenate `One-Time` and capitalize the defined product object in headings, labels and explanatory text.

Do not use in user-facing copy:

- Voucher Code
- Voucher Codes
- One Time Code
- Onetime Code
- Onetimecode

## Approved short forms

Short action labels may use `Code` or `Codes` when the surrounding workflow makes the object unambiguous:

- Import Codes
- Distribute Code
- Copy Code

Explanatory text should prefer `One-Time Code`.

## Workflow terms

| Concept | English source language | German target language |
| --- | --- | --- |
| Product | Voucher Manager | final name pending |
| Object | One-Time Code | Einmalcode |
| Object plural | One-Time Codes | Einmalcodes |
| Pool | Pool | Pool |
| Inventory | Inventory | Bestand |
| Distribution | Distribution | Ausgabe |
| Activity | Activity | Aktivität |
| Available | Available | Verfügbar |
| Assigned | Assigned | Ausgegeben |
| Settings | Settings | Einstellungen |
| Danger Zone | Danger Zone | Gefahrenbereich |

## Tone

Use concise, neutral-professional language.

Avoid addressing the administrator as `you`, `your`, `Sie` or `du` when an impersonal formulation is natural.

Preferred:

- `Create the first pool`
- `Assigned One-Time Code`
- `Select Pool`

Avoid:

- `Create your first pool`
- `Your assigned code`

First-person acknowledgement remains acceptable for explicit destructive confirmations, for example:

`I understand that this permanently deletes...`

## Privacy language

Use `One-Time Code value` for the complete sensitive value.

Use `masked reference` for Inventory presentation.

Never imply that complete values are available in Inventory or Activity.

## Internal language

Do not rename stable PHP classes, namespaces, repositories, database fields, event names or stored status values merely to mirror UI terminology.
