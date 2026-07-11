# Quality Gate

Voucher Manager must pass the quality gate before a release is considered publishable.

## Checks

1. Composer metadata validation
2. PHP syntax on PHP 8.1, 8.2, 8.3 and 8.4
3. Required plugin structure
4. Plugin header and internal version consistency
5. Autoload smoke test for critical classes
6. Release ZIP build
7. Release ZIP content and root-folder validation

The validated ZIP is uploaded as a GitHub Actions artifact from the PHP 8.4 job.

A green source build is not enough: the release artifact itself must also pass.
