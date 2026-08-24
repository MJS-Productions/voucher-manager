# Testing

Run the complete local quality gate:

```bash
composer install
composer quality
```

The generated WordPress.org-compatible plugin package is available at:

```text
dist/mjs-productions-voucher-manager.zip
```

## Manual release smoke test

For the WordPress.org candidate, test the validated package using the `mjs-productions-voucher-manager` plugin directory identity. Pre-directory internal builds that used the old `voucher-manager` directory are not an in-place ZIP upgrade target.

Before publishing:

1. Install the validated package on a clean or dedicated WordPress test site.
2. Confirm WordPress recognizes **MJS-Productions Voucher Manager**.
3. Activate the plugin.
4. Confirm the Voucher Manager administration menu opens without errors.
5. Test pool management.
6. Test TXT/CSV import.
7. Test code distribution.
8. Confirm no fatal errors or PHP warnings.

## Golden path integration test

The framework-free integration test runs with in-memory repositories and validates:

```text
pool creation -> TXT import -> duplicate handling -> two unique distributions -> empty-pool failure -> logs -> protected rollback
```

Run it directly:

```bash
php tests/Integration/GoldenPathTest.php
```

This test covers domain behaviour. It does not replace the manual WordPress activation and workflow smoke test.

## State integrity

```bash
composer test:state-integrity
```

This verifies allowed and forbidden code lifecycle transitions.

## Operational logging and error boundaries

```bash
composer test:operational-logging
```

This verifies privacy-safe context, controlled exception handling and resilient
logging.
