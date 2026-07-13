# Import Experience

Sprint 6 Part 3 guides administrators through code imports without changing the established import engine.

## Upload guidance

- TXT reads one code per line.
- CSV reads the first column and ignores a common header.
- Duplicate codes are skipped.
- Empty and invalid rows are counted but not imported.
- Uploads are limited to 10 MB.
- Destination pools show available and total inventory.

## Results and history

Completed imports show codes added, skipped rows, invalid rows and total rows processed. A zero-added result explains likely duplicate or invalid input. `ImportViewModel` owns human-readable status and result presentation.

## Rollback safety

Import history links to a dedicated rollback review page. Confirmed execution uses POST, `manage_options`, a WordPress nonce and an explicit acknowledgement. If any code from that import has been assigned, rollback remains fully blocked and no codes are removed.
