# ADR 0001: Project structure

- Status: Accepted
- Date: 2026-07-10

Use a namespaced, Composer-autoloaded `src/` directory. WordPress-specific integration lives at the boundary, while future voucher-domain logic avoids direct WordPress dependencies where practical.
