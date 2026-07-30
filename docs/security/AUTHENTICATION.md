# Authentication and privileged MFA

Pulse authenticates active accounts with non-enumerating, throttled password workflows, rotates session identifiers at login and MFA boundaries, and requires email verification for administration. Administrative privilege is derived from the live permission graph, not role labels.

Privileged users enroll an RFC 6238-compatible, SHA-1, six-digit, 30-second TOTP authenticator after recent password confirmation. The setup secret appears only in the enrollment response and is encrypted in the database. Confirmation creates eight random recovery codes; only password hashes are retained. Codes are single use, and regeneration invalidates the old set. TOTP replay in the same time step is rejected. MFA secrets and recovery-code plaintext are excluded from model serialization and audit context.

Every verified privileged route runs the MFA middleware. A missing enrollment redirects to management; a configured user without current-session completion receives the challenge. Successful confirmation/challenge rotates the session ID. Failed challenges are rate limited and audited.

## Lost-authenticator procedure

1. Use one offline recovery code at the challenge, then password-confirm and regenerate codes.
2. If no code remains, contact a different active operator who holds `users.manage` and has completed MFA.
3. The operator verifies identity through an approved out-of-band organizational process, password-confirms, and performs the audited administrative MFA reset.
4. The affected user remains unable to use privileged capabilities until completing fresh enrollment.

Self-service administrative reset is rejected. SMS is not supported. Database operators should not decrypt or manually replace MFA fields.
