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
