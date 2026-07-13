# Pool Experience

The Pools screen treats a pool as an inventory rather than a database row.

## Inventory states

- `Ready`: active and above the warning threshold.
- `Low stock`: active and at or below the warning threshold.
- `Empty`: active with no available codes.
- `Inactive`: distribution is paused.

## Inventory counters

Each pool shows:

- available codes;
- distributed codes;
- total codes.

## Actions

Ready pools lead to distribution. Empty, low-stock-without-available-inventory,
and inactive pools guide users toward importing codes where appropriate. Edit
and activation controls remain available as secondary actions.

## Test

```bash
composer test:pool-experience
```
