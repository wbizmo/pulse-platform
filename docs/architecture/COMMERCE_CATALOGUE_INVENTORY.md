# Commerce catalogue and inventory (M7)

M7 is an independent modular-monolith Commerce capability. `ProductCategory` is a flat commerce taxonomy separate from Blog categories. A `Product` has a typed `draft`, `active`, or `archived` lifecycle; only active products with an active variant are public. Every purchasable unit is a `ProductVariant`. Products do not own prices: each variant is the sole authority for its integer `price_minor` and uppercase allow-listed ISO currency. SKUs retain operator-facing capitalization while a globally unique uppercase `normalized_sku` prevents case-equivalent reuse, including after archival. Variant options are a sorted, bounded string map with a per-product fingerprint.

Product images are managed Media records only. Featured media uses a restrictive foreign key and galleries use parent-scoped unique positions. Media deletion refuses featured or gallery dependencies. Catalogue copy is plain escaped text; arbitrary HTML and remote image URLs are not accepted.

## Inventory invariants

A variant materializes `on_hand` and `reserved`; `available = on_hand - reserved`. Actions lock the variant row with `lockForUpdate` inside a transaction, reject `on_hand < 0`, `reserved < 0`, `reserved > on_hand`, and append an immutable `InventoryLedgerEntry` in the same transaction. Historical entries have no update/delete application path; corrections are compensating entries. Public pages disclose only “In stock” or “Out of stock”, never exact quantities.

Reservations have UUID tokens and `active`, `released`, `expired`, or `consumed` states. Creation locks the balance before checking availability. Release/expiry decrement reserved; consumption decrements both reserved and on-hand. Finalization is idempotent and appends exactly one ledger movement. `commerce:expire-reservations --batch=100` processes an expiry-ordered bounded batch and is scheduled without overlap. MySQL row locks are the production concurrency authority; SQLite tests prove sequential competing-reservation and transactional invariants but cannot prove engine-level parallel row locking.

Administration is independently protected by `commerce.products.manage` and `commerce.inventory.manage`, plus the established verified-account and privileged-MFA boundary. Catalogue selectors are bounded and lists paginated. Public routes live below `/catalogue` and render through all active first-party theme layouts.

M7 deliberately contains no carts, checkout, addresses, shipping, coupons, taxes, orders, payments, gateways, refunds, or fake purchase actions. Those remain M8/M9.
