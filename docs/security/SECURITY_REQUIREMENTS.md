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
