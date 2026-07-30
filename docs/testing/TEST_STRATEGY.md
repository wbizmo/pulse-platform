# Test strategy

Use PHPUnit at unit, feature and integration layers. Unit tests cover values, state machines, schema validation and pure domain rules. Feature tests cover HTTP validation, CSRF/session behavior, policy/direct-access denial, CRUD and rendered UI restrictions. Integration tests cover database constraints/locking, adapters, queued jobs, encryption and webhook inbox behavior. Add regression tests for repaired defects.

Maintain an authorization matrix for anonymous, disabled, unverified, each permission combination and super-administrator behavior across routes, UI, jobs, commands, exports/downloads and sensitive actions. Payment contract tests run identical scenarios against every adapter: success/pending/failure, timeout, malformed/rate-limited responses, currency/amount mismatch, idempotency, duplicate/out-of-order webhooks, refunds/disputes and reconciliation. Exercise simultaneous stock purchase/reservation expiry and duplicate checkout.

Frontend interaction/browser coverage validates builder drag/reorder/nesting/recovery/concurrency, dialogs/toasts, uploads, keyboard/focus behavior, safe escaping, reduced motion, and desktop/tablet/mobile layouts. Use real sandbox gateways only when credentials are explicitly supplied; deterministic fakes may test contracts but never masquerade as shipped integrations.

Baseline commands from `cms/`: `composer validate --strict`, `vendor/bin/pint --test`, `php artisan test`, `php artisan route:list`, and `npm run build`. Release checks add clean database migrations/seed, queue and scheduler exercise, application login/workflow smoke tests, security/config review, and clean import/boot against release SQL. Record exact commands and honest outcomes in `NEXT.md`; never infer success from compilation.


The M1 matrix exercises MFA enrollment enforcement, current-session challenge state, permission-driven enforcement changes, password confirmation, encrypted and hidden secrets, one-way single-use recovery codes, plus denial precedence for disabled and unverified identities. Lifecycle regressions retain signed verification tampering, session and profile ownership, and final-super-administrator invariants.


M2 rendered-interface feature tests cover permission-aware and active navigation, mobile drawer landmarks, escaped flash-to-toast rendering, validation summaries, and destructive dialog triggers. Production Vite compilation is a required frontend gate; browser interaction remains a separate, explicitly reported gate.


M2 follow-up coverage renders a representative legacy content administration screen, verifies its custom destructive-dialog trigger, and scans administration views and JavaScript for prohibited native `alert()` or `confirm()` calls.
