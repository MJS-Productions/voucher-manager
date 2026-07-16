# ADR 0030: Localization Context, Placeholder and Plural Contract

## Status

Accepted for Sprint 9 Part 3.2.

## Context

A translation-ready text domain is not sufficient when short labels are ambiguous, placeholders lack translator guidance or English plural assumptions are embedded in UI copy.

## Decision

- Add translator context to ambiguous short labels using `_x()` or escaped context helpers.
- Use numbered placeholders for multi-value translated sentences.
- Place `translators:` comments directly before non-obvious placeholder calls.
- Use `_n()` for count-dependent nouns.
- Keep technical identifiers and diagnostics outside the catalog.
- Protect these rules with a pre-build Translation Readiness gate.

## Consequences

Catalog extraction can provide translators with context and placeholder guidance. Singular and plural UI output remains grammatically valid before the German catalog is introduced.
