# Sprint 6 Release Readiness

Sprint 6 — **The First Experience** is complete at the development level.

The release version is intentionally not selected by this document. Version identity remains `0.5.0-alpha` until the dedicated Sprint 6 release review chooses the next release version and updates all version-bearing files together.

## Completed experience areas

1. Navigation and Dashboard
2. Pool Experience
3. Pool Lifecycle and Danger Zone
4. Import Experience
5. Distribution Experience
6. Operational Visibility
7. Regression Tests and Documentation

## Automated quality boundary

`composer quality` must pass before a release artifact is considered for WordPress testing.

The chain covers:

- PHP syntax;
- plugin structure;
- plugin header and version consistency;
- autoload smoke testing;
- Golden Path integration;
- code-state integrity;
- operational logging and privacy sanitization;
- Dashboard Experience;
- Pool Experience;
- Pool Lifecycle integrity;
- Import Experience;
- Distribution Experience;
- Operational Visibility;
- Sprint 6 cross-layer regression and documentation consistency;
- release artifact build and validation.

GitHub Actions must pass the Quality job on PHP 8.1, 8.2, 8.3 and 8.4.

## Manual WordPress smoke-test matrix

Use the `voucher-manager.zip` artifact built and validated by GitHub Actions.

### Update and navigation

- WordPress recognizes the package as an update of Voucher Manager.
- Existing pools, imports and codes remain available.
- Dashboard, Pools, Import, Distribution and Activity open without a Critical Error.

### Dashboard and activity

- Inventory metrics match the current data.
- Recent Activity uses human-readable labels rather than the generic fallback for stable events.
- `View all activity` opens the complete Activity history.
- Activity filters work for area and outcome.
- No voucher values, raw context, exception messages or personal data are displayed.

### Pool lifecycle

- Deactivation preserves the pool and all associated data.
- Delete available codes opens a dedicated review page.
- Unacknowledged available-code deletion is rejected.
- Available-code deletion preserves assigned codes.
- Full deletion rejects an incorrect pool name.
- Confirmed full deletion removes the pool, codes and import records.

### Import

- TXT import accepts one code per line.
- CSV import reads codes from the first column.
- Result summaries correctly report added, skipped, invalid and processed rows.
- Rollback opens a dedicated review page and requires acknowledgement.
- Rollback remains blocked when the import contains assigned codes.

### Distribution

- Only active pools with available inventory are selectable.
- Successful distribution presents one assigned code and a Copy code action.
- Refresh does not distribute another code.
- Remaining inventory guidance is correct.
- The final distribution changes to empty-inventory guidance.

## Release review gate

Before selecting and publishing the next version:

1. Complete `composer quality`.
2. Confirm all GitHub PHP jobs are green.
3. Install the validated GitHub Actions artifact in WordPress.
4. Complete the smoke-test matrix.
5. Review `CHANGELOG.md`, `README.md`, release notes and version-bearing files.
6. Select the next version deliberately.
7. Update version identity consistently.
8. Re-run the complete Quality Gate.
9. Build and smoke-test the final versioned artifact.
10. Publish the GitHub Release only after the final artifact passes.
