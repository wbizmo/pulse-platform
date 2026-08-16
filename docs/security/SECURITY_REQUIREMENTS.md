# Security requirements

## Baseline

Assume every external value is hostile. Use allow-list validation, contextual output escaping/sanitization, parameterized persistence, guarded assignment, CSRF, session regeneration, secure cookie configuration, rate limits, non-enumerating authentication errors, safe local redirects, least privilege and deny-by-default authorization. Enforce capabilities in routes/controllers/requests/policies/services/jobs/commands/APIs and not merely navigation or buttons.

Explicitly test broken access control/IDOR, privilege escalation, CSRF, stored/reflected XSS, SQL/command injection, mass assignment, traversal/ZIP-slip, SSRF, open redirects, unsafe image/SVG upload, session fixation, brute force, CSV formulas and sensitive-log leakage. Prevent deletion, disabling or demotion of the final super administrator and prevent permission delegation beyond the actor's authority.

## Secrets and sensitive data

Never commit or log credentials. Encrypt provider secrets, reveal them only on initial entry, display only redacted metadata afterward, require step-up confirmation for changes, and audit access/change without secret values. Redact authorization headers, cookies, tokens, passwords, keys, signatures, personal data and payment details from logs, exceptions, audits and UI.

## Payments and extensibility

Never accept browser returns as payment proof. Verify provider server responses/webhook signatures, timestamps and amounts; record unique provider event IDs; reject replay; make processing duplicate/out-of-order safe and transactional. Do not retry charges without provider idempotency. Validate plugin/theme manifests, paths, archives and compatibility; contain plugin failures and prohibit runtime arbitrary upload/execution without narrowly controlled authorization.

## Audit and response

Append meaningful actor, capability, target, outcome, request correlation, safe context and timestamp for authentication, RBAC, configuration/secret, plugin/theme, content publication, export/download, inventory, order/payment/refund and operational actions. Protect audit/log access with dedicated permissions and tamper-resistant retention. Security regressions block release.


## Privileged MFA

A user is privileged whenever current normalized roles grant any administrative permission, including through the super-administrator capability; role names are not the enforcement input. Privileged routes require a verified active account, confirmed TOTP configuration, and a challenge completed in the current server-side session. Secrets are cryptographically random, encrypted at rest, hidden from serialization, and displayed only by the enrollment response. Recovery codes are random, individually one-way hashed, displayed only on generation, and removed after use. TOTP permits an adjacent 30-second window for clock skew and rejects current-step reuse. Challenges are throttled and all lifecycle outcomes are audited without secret material.

Enrollment, disablement, replacement, and recovery-code regeneration require recent password confirmation. A user who loses an authenticator should use one stored recovery code and immediately regenerate the set. If both factors are lost, a different active operator with `users.manage`, completed MFA, and recent password confirmation may reset MFA after verifying identity out of band. The affected privileged user is denied privileged access until re-enrollment. An operator cannot administratively reset their own MFA. SMS is not supported.

## Content publication

Public queries must use the shared publication-eligibility scope. Unpublished preview requires an authenticated, authorized account and an unexpired signed URL, and responses must be private, uncached, and noindex. Slugs reject reserved system paths and retain database uniqueness. Stale editor versions must fail closed rather than overwrite newer content.

Taxonomy creation, update, deletion, and post assignment require `taxonomy.manage` independently of post editing. Submitted identifiers must exist and be distinct, archive queries must use centralized public visibility, and assigned taxonomy records must not be deleted. Category and tag names and slugs are normalized and protected by per-type database uniqueness.

## Managed media

Administrative media upload is capability, verified-account, active-account, and privileged-MFA protected. Accepted content is limited to configured-size JPEG, PNG, WebP, and GIF images that pass server-side MIME, dimension, pixel-count, and decode checks. SVG and non-image documents are rejected. Storage names and directories are server-controlled; original names are display metadata only. Restrictive foreign keys and application checks prevent deletion of referenced media.

## Navigation

Menu administration requires `menus.manage` and privileged-session controls. Custom links accept only safe root-relative, HTTP, or HTTPS destinations. Public page links use the shared publication scope and current slug. Restrictive foreign keys prevent orphaned page items, and new-tab links render with `noopener noreferrer`.


## Search metadata

Global and per-content SEO mutation requires `seo.manage`; ordinary content editors cannot submit SEO fields and updates preserve stored metadata. Canonical overrides are restricted to absolute HTTP/HTTPS URLs on the configured public origin. Robots content is bounded, line-ending normalized, and rejects NUL and dangerous controls. Metadata is HTML escaped and structured data uses defensive JSON encoding.

## Forms security

Public forms accept only persisted field keys and compile validation exclusively from a closed, bounded schema. They reject scalar/array confusion, unexpected keys, oversized bodies, inactive targets, invalid options and hostile types; CSRF, throttling and a honeypot provide abuse controls. Submission values are escaped at presentation and excluded from audits and URLs. Forms administration and personal-data inbox access require `forms.manage` and privileged MFA.

## Builder security

Builder documents accept only schema version 1 and the server registry's exact node, property, responsive-setting and nesting shapes. UUID identity is unique across the bounded forest (100 nodes, depth 4, 24 children); request JSON is limited to 128 KiB and collection/string limits are block-specific. Links use the shared safe public-link policy, video is restricted to HTTPS YouTube/Vimeo destinations, and images reference existing managed raster media. Raw HTML, iframe markup, executable embeds, arbitrary CSS/classes and unknown blocks are not supported. Invalid or legacy documents fail closed at public and private-preview rendering without being mutated. Every save carries Page `lock_version`, updates atomically, and records only schema/count/version audit metadata.

## Theme platform
Theme identity/view selection comes only from the first-party registry. Hostile persisted settings pass a closed schema; arbitrary CSS, URL schemes, fonts, classes, objects and unknown keys are prohibited. Branding uses protected managed media. Activation/rollback require the full theme administration boundary, locking, atomic singleton state and bounded auditing. Preview is nonpersistent, private/no-store and noindex.

## Plugin runtime

Executable plugin identity and contributions are first-party code only. Administrative input cannot select classes, callbacks, routes, paths, views or executable content. Lifecycle/settings require the canonical verified, active, MFA-complete `plugins.manage` boundary and CSRF. Settings are closed and typed; permissions are slug-namespaced and cannot override core authority; audits/logs contain bounded redacted metadata.

## M7 Commerce

Catalogue and inventory mutations require separate canonical permissions, verified active identity, privileged MFA and CSRF. SKU/currency/money/options/media inputs use closed bounded validation. Public catalogue queries expose active records and qualitative availability only. Stock changes lock and transact balances with an append-only ledger; media dependencies block deletion.


## Commerce transaction controls (implemented M8)
Guest cart/order capabilities are random, stored only as hashes, transported in HttpOnly first-party cookies, and compared without exposing database IDs. Checkout rejects unknown address fields and browser totals, snapshots escaped Unicode PII, uses no-store/noindex customer responses, and excludes PII/secrets from audits. Administrative orders require verified identity, privileged MFA and dedicated permission.

## M9 payment boundary

Gateway secrets use dedicated encrypted storage and replacement-only forms. Public webhooks are POST-only, size/content-type/rate bounded, authenticated from original bytes before persistence, and replay protected. Pulse never collects PAN/CVV or trusts browser amount, currency, provider status, return URLs, or callback success. Audits and operator views contain normalized identifiers and replacement metadata only.
