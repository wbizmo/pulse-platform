# Payments and refunds (M9)

M9 adds a first-class gateway-neutral Payments capability. Each immutable M8 Order has at most one logical `Payment`, whose amount and currency are copied from the Order. Multiple `PaymentAttempt` records permit retries without repricing or creating another Order. Canonical payment and refund enums prevent provider status strings from directly controlling domain state. Provider references, safe action metadata, and bounded errors are retained; PAN, CVV, credentials, OAuth tokens, client secrets, raw webhook bodies, and full provider responses are not.

## Gateways and credentials

`PaymentGatewayRegistry` is a closed registry for Stripe, PayPal, Flutterwave, and Paystack. Availability requires an enabled encrypted configuration, required secrets, and an explicit currency allow-list. Unknown names fail closed. Laravel encrypted casts protect API and webhook secrets; administration never pre-populates them and audits only gateway, mode, currencies, enabled state, and replacement indicators. Provider base URLs are code-owned and outbound calls have TLS, connect/response timeouts, bounded safe retries, JSON expectations, and mutation idempotency identities.

Official protocol drift was checked on 2026-08-16 against provider documentation:

| Gateway | API / endpoint family | Authentication and success authority | Refund and idempotency | Environment |
|---|---|---|---|---|
| Stripe | PaymentIntents and Refunds under `https://api.stripe.com/v1` | `Stripe-Signature` timestamped HMAC over raw bytes; a server retrieval confirms PaymentIntent status, amount and currency | Refunds API; `Idempotency-Key` | credential-selected test/live |
| PayPal | OAuth2, Orders v2, Payments v2; `api-m.sandbox.paypal.com` / `api-m.paypal.com` | official `verify-webhook-signature` API using webhook ID and transmission headers; server Order/capture retrieval is authority | captured-payment refund; `PayPal-Request-Id` | explicit sandbox/live |
| Flutterwave | Standard hosted Payments and Transactions v3 under `api.flutterwave.com/v3` | current base64 HMAC-SHA256 `flutterwave-signature` over raw bytes plus transaction verification | transaction refund; stable `X-Idempotency-Key` | credential/account-selected test/live |
| Paystack | transaction initialize/verify and refund under `api.paystack.co` | `x-paystack-signature` HMAC-SHA512 over raw bytes plus transaction verification | refund API and local/provider idempotency | credential-selected test/live |

Protocol authority: [Stripe signatures](https://docs.stripe.com/webhooks/signature), [Stripe refunds](https://docs.stripe.com/refunds), [PayPal Orders v2](https://developer.paypal.com/docs/api/orders/v2/), [PayPal webhook verification](https://developer.paypal.com/api/rest/webhooks/rest/), [Flutterwave webhooks](https://developer.flutterwave.com/docs/integration-guides/webhooks), [Flutterwave refunds](https://developer.flutterwave.com/reference/refund-a-transaction), [Paystack transactions](https://paystack.com/docs/api/transaction/), [Paystack webhooks](https://paystack.com/docs/payments/webhooks/), and [Paystack refunds](https://paystack.com/docs/api/refund/).

## Fulfilment, inbox, and races

Browser returns are navigation plus bounded server verification, never proof by themselves. Public POST-only provider routes impose a 256 KiB limit, JSON requirement, rate limit, provider signature verification over original bytes, and a unique `(gateway, external_event_id)` replay key. Only hashes and normalized identifiers are stored. Reception/authentication/persistence is separate from idempotent processing.

`ConfirmSuccessfulPayment` locks attempt, Payment, and Order; validates captured amount/currency; applies a single successful transition; consumes every M7 reservation; consumes reserved coupon capacity; and appends Order history in one transaction. Duplicate or out-of-order success is harmless. A success observed after cancellation/expiry records a succeeded attempt and reconciliation flag but neither changes the Order nor consumes released stock. Order cancellation/expiry locks Payment and refuses release after authoritative success. Pending attempts do not extend M8 expiry automatically; unresolved provider state is recovered through reconciliation rather than reserving stock forever.

## Refunds, disputes, and reconciliation

Refunds use integer minor units, stable local/provider idempotency, Payment locking, and count requested/in-flight/succeeded amounts against captured value to prevent over-refund. Only provider-successful refunds increase `refunded_minor` and transition an Order to `partially_refunded` or `refunded`; refunds never restock or restore coupon capacity. Disputes are normalized bounded records keyed by gateway/provider ID and are read-only in administration; they never trigger an automatic refund.

`payments:reconcile --gateway=<slug> --batch=100` processes at most 500 unresolved attempts per run, is retry-safe, and marks ambiguous failures for later operator review. Web requests operate on one Payment/refund rather than launching unbounded scans.

## Customer/admin and data boundary

The capability-protected, private/no-store/noindex payment page exposes only configured currency-compatible choices. Provider-hosted redirects are used for PayPal, Flutterwave, and Paystack; Stripe returns only its publishable collection material, never its secret key. Customer status is friendly and bounded. Payment configuration/list/detail and refunds sit behind active account, verified email, canonical RBAC, privileged MFA, and CSRF. `commerce.payments.manage` does not imply `commerce.refunds.manage`.

No live/sandbox credentials were present in the repository environment, so live provider certification remains an owner/environment release gate. HTTP fakes and locally derived signatures are automated evidence, not live certification. Real MySQL concurrency and TD-005 browser/assistive-technology checks also remain release gates.
