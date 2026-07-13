# Code State Model

## Active workflow

Voucher Manager currently exposes one distribution transition:

```text
available -> assigned
```

The transition is validated centrally before the repository claims a code.

## Prepared states

`reserved`, `expired` and `cancelled` are deliberately not exposed in the
WordPress interface yet. They exist so future integrations can be designed
against a stable domain vocabulary.

## Integrity rules

- A state cannot transition to itself.
- Assigned codes cannot return to the available pool.
- Expired codes are terminal.
- Cancelled codes are terminal.
- A reserved code may return to available before assignment.
- Persistence code must use `CodeStatus` values rather than ad-hoc strings.

Run:

```bash
composer test:state-integrity
```
