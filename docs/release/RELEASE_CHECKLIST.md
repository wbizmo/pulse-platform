# Release checklist

A release owner records command, commit, environment and artifact checksums. Required gates are strict Composer validation/Pint/full tests/routes/schedule/build/diff check/dependency audits; clean MySQL migrations/system seed/installer; genuine parallel commerce/payment races; release-SQL generation, forbidden-data scan, checksum, second-database import and schema comparison; production no-dev/cache/HTTPS HTTP smoke with empty unexplained stderr; disposable backup restore; M10 upgrade/rollback rehearsal; secret/config/route/auth/upload/path/redirect/webhook/payment review; and browser viewport, keyboard and automated accessibility flows.

The repository must contain no runtime artifacts, demo credentials, debug routes or secrets. `/up` stays minimal and Operations stays protected. Human screen-reader testing and live provider certification must be recorded as external owner gates when unavailable; they must never be inferred from automation.

## Current M11 qualification record

The code and local SQLite gates do not constitute the required genuine MySQL, parallel-race, clean-import, restore, browser/AT, or live-provider evidence. Those gates remain explicitly unpassed until the release workflow/operator record supplies them. Therefore M11 must not be tagged complete solely from a development checkout.
