# Testing

Run the complete local quality gate:

```bash
composer install
composer quality
```

The generated plugin package is available at:

```text
dist/voucher-manager.zip
```

## Manual release smoke test

Before publishing:

1. Update an existing WordPress installation.
2. Confirm WordPress recognizes the package as the same plugin.
3. Activate the plugin.
4. Confirm existing pools and codes remain.
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

This test covers domain behaviour. It does not replace the manual WordPress upgrade and activation smoke test.

## State integrity

```bash
composer test:state-integrity
```

This verifies allowed and forbidden code lifecycle transitions.
