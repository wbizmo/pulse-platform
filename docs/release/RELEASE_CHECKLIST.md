# Release checklist

A release owner records command, commit, environment and artifact checksums. Required gates are strict Composer validation/Pint/full tests/routes/schedule/build/diff check/dependency audits; clean MySQL migrations/system seed/installer; genuine parallel commerce/payment races; release-SQL generation, forbidden-data scan, checksum, second-database import and schema comparison; production no-dev/cache/HTTPS HTTP smoke with empty unexplained stderr; disposable backup restore; M10 upgrade/rollback rehearsal; secret/config/route/auth/upload/path/redirect/webhook/payment review; and browser viewport, keyboard and automated accessibility flows.

The repository must contain no runtime artifacts, demo credentials, debug routes or secrets. `/up` stays minimal and Operations stays protected. Human screen-reader testing and live provider certification must be recorded as external owner gates when unavailable; they must never be inferred from automation.

## Current M11 qualification record

The code and local SQLite gates do not constitute the required genuine MySQL, parallel-race, clean-import, restore, browser/AT, or live-provider evidence. Those gates remain explicitly unpassed until the release workflow/operator record supplies them. Therefore M11 must not be tagged complete solely from a development checkout.


## 2026-08-16 genuine MySQL qualification evidence

MySQL Community Server 8.0.46 (Ubuntu package `8.0.46-0ubuntu0.24.04.3`) was used with strict defaults and InnoDB foreign keys enabled. Clean migration initially failed on legacy same-timestamp child/parent ordering for themes, plugins, menus and categories; the migrations now defer only those foreign keys until all parent tables exist. Clean migration, production-safe seed and `pulse:preflight` subsequently passed. MySQL JSON normalization also exposed an order-sensitive Builder object-key check; exact-key validation is now order-independent and the complete MySQL suite passes 114 tests / 689 assertions.

The canonical `database/releases/pulse_cms_mysql.sql` contains data only for migrations, roles, permissions, their pivot, themes and plugins. Its SHA-256 is recorded beside it. Import into a second empty MySQL database reports every migration as run and `migrate --force` reports nothing to migrate. Schema DDL comparison found only MySQL's equivalent explicit inherited `CHARACTER SET` rendering after dump import, with tables, columns, types, nullability, defaults, indexes, unique constraints and foreign keys otherwise equivalent. First-admin installation on the imported database succeeded and a second invocation exited 1 before accepting new credentials.

This is incremental evidence, not release closure. Parallel race, restore, M10 upgrade/rollback, clean no-dev package/HTTP, browser/keyboard/automated accessibility, human AT and live-provider gates remain unpassed.
