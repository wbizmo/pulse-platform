# M5 Theme Platform contract

M5 defines exactly three executable first-party manifests in `ThemeRegistry`: Pulse Studio, Pulse Corporate and Pulse Commerce. Stable slugs map to closed renderer identities; database metadata is lifecycle state, not executable authority. Manifest/settings schema version 1 supports Builder schema 1. Unknown, retired, incompatible or malformed active state cannot select a view path and resolves to Pulse Studio.

Settings are a closed typed document normalized by `ThemeSettings`. Supported controls are managed logo/favicon images, six-digit colour tokens, allow-listed typography, density, header/footer, radius and width tokens, and a boolean back-to-top control. Unknown keys, arrays, arbitrary fonts/styles/CSS and forged media fail validation. Historical `custom_css` rows remain recoverable database data but no runtime or administration surface reads or executes them.

Activation locks the current and candidate rows in one transaction, validates manifest/version/settings, moves a unique nullable active slot, snapshots both sides, and audits the transition. Rollback is another validated activation transaction restoring the prior settings snapshot and linking history; it does not rewrite history. Managed images referenced by any saved theme cannot be deleted.

Authorized preview feeds a candidate manifest to the shared public Page/Builder renderer without changing activation. It is private/no-store and noindex. Administration inherits active-account, verified-email, `themes.manage`, CSRF and privileged MFA controls.

Legacy seeded themes are retained and retired during synchronization; they are not executable or deleted. Fresh seeds expose the canonical three. Each renderer uses distinct presentation tokens over shared content and Builder contracts. Commerce has no product/cart/checkout dependency or dead controls before commerce milestones.

Browser/assistive-technology coverage remains TD-005 and real MySQL remains a release gate. Third-party executable theme installation is not M5.
