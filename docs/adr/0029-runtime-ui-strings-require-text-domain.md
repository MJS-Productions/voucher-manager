# ADR 0029: Runtime UI Strings Require the Plugin Text Domain

## Status

Accepted for Sprint 9 Part 3.1.

## Context

Most administration copy was already translation-ready, but Distribution result messages from the Domain Service and English JavaScript fallback labels could bypass the translation catalog.

## Decision

- Every string intentionally rendered in the WordPress administration UI must use the `voucher-manager` text domain.
- JavaScript embedded in PHP templates must receive visible labels from translated HTML data rather than duplicate English literals.
- Internal exception diagnostics, stable event keys and persisted technical values remain untranslated.
- Add a regression gate that checks these runtime boundaries before the release artifact is built.

## Consequences

The English source language is now a reliable basis for catalog extraction. Context, placeholder and catalog work can proceed without carrying known raw runtime UI strings.
