# ADR 0032: Translation Artifact Integrity

## Status

Updated following WordPress.org plugin review.

## Context

The repository maintains reviewed gettext translation sources and uses deterministic compilation to detect stale or inconsistent translation artifacts.

For plugins distributed through the official WordPress.org directory, production translations are delivered through WordPress language packs. The plugin release package therefore does not need to bundle PO or MO catalogs or manually load its text domain.

## Decision

- Maintain POT and PO catalogs as reviewed repository source artifacts.
- Compile the repository MO verification artifact deterministically with `tools/compile-translations.php`.
- Expose the build through `composer translations`.
- Run translation compilation before the local Quality Gate and explicitly in GitHub Actions.
- Keep `tools/build-release.php` invoking translation compilation so repository translation integrity is verified even when the release builder is invoked directly.
- Compare the committed MO byte-for-byte with a fresh deterministic compilation.
- Do not bundle POT, PO or MO catalogs in the WordPress.org release artifact.
- Do not manually call `load_plugin_textdomain()` for WordPress.org translation delivery.
- Rely on WordPress language packs for production translations.

## Consequences

A stale or missing repository MO verification artifact blocks the Quality Gate and release build even though that artifact is not shipped in the WordPress.org package.

The same PO input produces the same MO bytes across supported PHP versions because timestamps and machine-specific metadata are not introduced by the compiler.

Future translation source changes must update the PO catalog; the repository MO artifact is generated rather than edited.

The production plugin package remains independent of bundled translation catalogs.
