# ADR 0023: Committed Distribution Claim Is Authoritative

## Status

Accepted for Sprint 8 Part 5.1.

## Context

The atomic voucher claim commits before remaining-inventory counting and Operational Activity persistence. A failure in either post-claim operation could previously escape the Distribution Service and make an already assigned voucher appear undistributed.

## Decision

Once `claim_next_available()` returns a voucher, Distribution success is authoritative.

Post-claim remaining-count and Activity failures are contained separately.

Remaining inventory becomes nullable so unknown state is not falsely represented as zero.

Post-claim failure reporting contains only a bounded stage identifier and exception class. Voucher values and exception messages are excluded.

Do not release or retry an already claimed voucher.

## Consequences

Operational metadata can degrade without hiding a committed voucher. Distribution preserves the strongest safety boundary: a claimed voucher remains assigned and the administrator still receives the successful business result.
