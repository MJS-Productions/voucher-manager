# ADR 0026: Final Distribution Safety Invariants

## Status

Accepted for Sprint 8 Part 5.4.

## Context

Distribution now contains separate protections for atomic voucher claims, post-claim failure containment, one-use request intents and one-time result delivery. VM-014 and VM-015 demonstrated that individually correct protections can still interact incorrectly under concurrent browser requests.

## Decision

Treat the complete Distribution lifecycle as one reviewed safety system and preserve five invariants:

1. One intent can pass successful intent consumption at most once.
2. One available voucher row can transition to assigned at most once.
3. A committed claim is the authoritative business outcome.
4. Racing responses for one intent may receive independent deliveries only for the same authoritative result.
5. Separate rendered forms remain separate deliberate Distribution actions.

The complete quality chain must include a static regression gate that verifies the ordering and presence of the intent, claim, post-claim and result-delivery boundaries.

The final manual release gate must include rapid double-click, spam-click, last-voucher concurrency and multi-tab tests.

## Consequences

Future Distribution changes are reviewed against lifecycle invariants rather than isolated methods. Green component tests are necessary but not sufficient; hostile browser-level smoke testing remains part of the release gate.
