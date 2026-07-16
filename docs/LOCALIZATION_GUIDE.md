# Localization Guide

## Source language

English is the source language. All visible runtime strings use the `voucher-manager` text domain.

## Context

Use `_x()` or an escaped context helper when a short label can have more than one meaning.

Examples:

- `Import` as an admin menu label
- `Available` as a One-Time Code status
- `Pool` as a Distribution form field
- `Result` as a Recent Imports table column

Context strings are translator-facing metadata and must remain concise and specific.

## Placeholders

Use numbered placeholders when a translation contains more than one dynamic value:

- `%1$s`
- `%2$d`

Add a `translators:` comment immediately before the translation call.

Do not concatenate translated sentence fragments with dynamic values when a complete sentence can be translated.

## Plurals

Use `_n()` for count-dependent nouns.

Plural handling now covers:

- import result counts;
- rollback deletion counts;
- remaining Distribution inventory;
- destructive Pool summaries;
- available-code deletion confirmations.

A localized count fragment may be inserted into a larger numbered-placeholder sentence when more than one independently pluralized noun is present.

## Escaping

Translate first, interpolate second, escape at output.

Use:

- `esc_html()` for completed dynamic messages;
- `esc_html_x()` for contextual plain-text labels;
- `esc_attr__()` or `esc_attr_x()` for attributes.

## Technical boundary

Do not translate:

- namespaces and class names;
- hooks, options or database identifiers;
- event keys and stored status values;
- internal exception diagnostics.

## German target language

The approved target terminology remains:

- One-Time Code → Einmalcode
- Inventory → Bestand
- Distribution → Ausgabe
- Activity → Aktivität
- Assigned → Ausgegeben
- Available → Verfügbar
