# Cart, checkout and orders (M8)

M8 adds a guest-only transaction boundary without treating administrative `User` records as customers. A browser receives a 43-character random cart capability in an HttpOnly, SameSite=Lax cookie; only its SHA-256 digest is persisted. A cart adopts the first variant currency and rejects mixed currencies. Lines uniquely identify a cart/variant pair, merge transactionally, retain only the observed authoritative price for stale-price detection, and never accept browser prices or reserve inventory.

## Checkout contract

The validated international address contract contains full name, optional organization, two address lines, locality, optional region/postal code, ISO country code, email and optional phone. Unicode is retained and Blade escapes presentation. Checkout accepts one configured coupon and one configured shipping method. Shipping zones match country and optional region. Shipping amounts and free thresholds are integer minor units in the cart currency. Tax rules match country/optional region by explicit priority and store rates in basis points. There are no jurisdiction claims or seeded tax defaults.

`MinorUnitMath` centralizes round-half-up integer percentage calculation. The sole totals equation is `total = subtotal - discount + tax + shipping`; every component is non-negative integer minor units in one currency. Discounts are capped at subtotal. Quotes re-read active products/variants, prices, currency, available stock, coupon validity/capacity, shipping and tax. Browser-supplied amounts and unknown address keys are rejected.

Final checkout requires a strong idempotency key. Its digest is unique and the canonical validated request fingerprint is persisted. An exact replay returns the same order; changed input fails. A single database transaction locks the cart and limited coupon, creates immutable order/item/address/rule snapshots, reserves variants in ascending identifier order through M7 `ReserveInventory`, records coupon capacity and converts the cart. Failure rolls everything back. MySQL row locks are the production concurrency authority; SQLite proves sequential invariants but not engine-level parallel locking.

## Order lifecycle and M9 boundary

Orders begin only in `awaiting_payment`. Their unpredictable public reference is not sufficient for access: the confirmation endpoint also requires a random HttpOnly SameSite=Strict capability whose digest is stored. Responses are private/no-store and noindex. No gateway, payment method, intent, charge, paid transition, refund, webhook or credential exists in M8.

Order items, customer/address data, totals, coupon, tax and shipping are immutable snapshots. `OrderStateHistory` is append-only business evidence, separate from privileged `AuditLog`. M8 permits only idempotent `awaiting_payment -> cancelled|expired`. Both transitions release active M7 reservations and coupon capacity exactly once. `commerce:expire-orders --batch=100` owns expiry of order-linked reservations; the raw M7 expiry command explicitly excludes those links. Administrative cancellation is MFA/RBAC protected and audited without address, email, checkout payload, tokens or idempotency secrets.

M9 takes an awaiting-payment Order, creates its own gateway-neutral attempt, verifies provider results, and alone gains authority to enter a future paid state and consume each linked reservation through M7 `FinalizeReservation(...Consumed)`. Failure policy must explicitly retain or release reservations; refunds/disputes remain M9. This contract requires no cart/order rewrite.

## Verification and remaining release gates

The dedicated `CartCheckoutOrdersTest` covers opaque capabilities, line merging/currency, integer rounding, authoritative snapshot totals, coupon capacity release, replay/body mismatch, oversell prevention, cancellation idempotence and guest IDOR. Full regressions retain M7 stock invariants. SQLite remains insufficient proof of MySQL parallel row locking, and TD-005 still requires real browser/assistive-technology interaction.
