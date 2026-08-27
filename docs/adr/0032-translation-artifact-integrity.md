# ADR 0032: Translation Artifact Integrity

## Status

Updated for shared Localization infrastructure adoption.

## Context

The repository maintains reviewed gettext translation artifacts for development
and verification.

Reusable WordPress localization operations are provided by
`mjs-productions/mjs-quality` and consumed through `tools/localization.php`.

The shared workflow generates the POT catalog from current source strings,
synchronizes the reviewed PO catalog and compiles the repository MO verification
artifact. It also provides non-destructive freshness checks and translation
completeness validation.

For plugins distributed through the official WordPress.org directory,
production translations are delivered through WordPress language packs. The
plugin release package therefore does not bundle POT, PO or MO catalogs and
does not manually load its text domain.

## Decision

- Maintain POT and PO catalogs as reviewed repository translation artifacts.
- Maintain the generated MO catalog as a repository verification artifact.
- Use the shared Localization infrastructure from
  `mjs-productions/mjs-quality`.
- Keep Voucher Manager-specific Localization configuration in
  `tools/localization.php`.
- Expose translation maintenance through `composer translations`.
- Expose non-destructive artifact freshness verification through
  `composer translations:check`.
- Expose translation completeness validation through
  `composer translations:validate`.
- Run freshness and translation validation in a dedicated GitHub Actions
  Localization job with WP-CLI available there.
- Do not regenerate translation artifacts as part of the normal PHP Quality
  Gate.
- Do not regenerate translation artifacts inside `tools/build-release.php`.
- Require committed translation artifacts to be current before release through
  the dedicated Localization quality gate.
- Do not bundle POT, PO or MO catalogs in the WordPress.org release artifact.
- Do not manually call `load_plugin_textdomain()` for WordPress.org translation
  delivery.
- Rely on WordPress language packs for production translations.

## Consequences

Stale POT, PO or MO repository artifacts fail the dedicated Localization quality
gate.

Translation maintenance uses the same reusable Localization infrastructure as
other MJS-Productions WordPress products while Voucher Manager retains ownership
of its text domain, translation content and packaging policy.

The normal PHP Quality Gate does not require WP-CLI. WP-CLI is required only
where the shared Localization workflow performs catalog generation,
synchronization or freshness verification.

Future source-string changes require the repository translation artifacts to be
updated through the shared Localization workflow.

The production WordPress.org plugin package remains independent of bundled
translation catalogs.
