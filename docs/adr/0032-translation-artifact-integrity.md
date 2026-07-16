# ADR 0032: Translation Artifact Integrity

## Status

Accepted for Sprint 10 Part 1.

## Context

WordPress loads compiled GNU gettext `.mo` files at runtime. The editable `.po` catalog can therefore be correct while WordPress still displays stale translations from an older `.mo` artifact.

Manual compilation is easy to forget and does not provide a reproducible release boundary.

## Decision

- Maintain POT and PO catalogs as reviewed source artifacts.
- Compile bundled MO files deterministically with `tools/compile-translations.php`.
- Expose the build through `composer translations`.
- Run translation compilation before the local Quality Gate and explicitly in GitHub Actions.
- Make `tools/build-release.php` compile translations even when invoked directly.
- Compare the committed MO byte-for-byte with a fresh deterministic compilation.
- Require POT, PO and MO files in the validated WordPress release artifact.

## Consequences

A stale or missing MO file blocks the Quality Gate and the release build.

The same PO input produces the same MO bytes across supported PHP versions because timestamps and machine-specific metadata are not introduced by the compiler.

Future translation source changes must update the PO catalog; the MO artifact is generated rather than edited.
