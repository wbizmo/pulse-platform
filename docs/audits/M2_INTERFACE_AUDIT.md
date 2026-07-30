# M2 interface audit

Date: 2026-07-30. Inspected Blade views, direct admin CSS/JavaScript, Vite inputs/configuration, route permissions, identity and RBAC feature coverage, forms, tables, pagination, flash/error output, and destructive controls.

## Observed before this slice

Login and the former admin layout loaded Google fonts and Material Symbols at runtime while identity subpages used a separate compressed layout. A 2,649-line directly served stylesheet mixed primitive values, page rules, duplicated component styles, large radii/gradients, and no documented layers; the Vite stylesheet was almost unused. The ten-line script only toggled a sidebar class, without overlay, Escape, focus, or state semantics. The shell had an unauthorized disconnected Site Health link, ungrouped navigation, duplicated Pages links, and no global feedback/dialog regions.

Login, recovery, verification, MFA enrollment/challenge/recovery/management, profile/sessions, dashboard, users, and roles used three incompatible class vocabularies. Errors and flash statuses varied between bare paragraphs, Bootstrap-like classes without Bootstrap, and cards. Users/roles had pagination but compressed table/form markup, weak mobile behavior, no reusable status badges/empty states, and immediate destructive submissions. The dashboard advertised hard-coded counts, health, and inactive quick actions.

Pages, media, menus, posts, categories/tags, themes, plugins, settings, SEO, and builder screens inherit the administration layout and canonical backend route permissions, but their internal markup remains on legacy `pulse-*` classes. Those module workflows were inspected but deliberately not represented as complete or secure merely through visual migration; their domain hardening belongs to their dependency-ordered milestones. Frontend/public views also remain outside this foundation slice.

## Resolved in this slice

The remote font/icon dependency, obsolete direct bundles, disconnected Site Health entry, fake dashboard metrics/actions, native immediate destructive submissions in M1 screens, and divergent identity layouts were removed. Tokens, Vite assets, shared shell, safe toast/dialog JavaScript, accessible error output, responsive M1 tables/forms, permission-grouped navigation, and reusable Blade primitives now provide the migration target.

## Follow-up slice

An interim bridge mapped the legacy module vocabulary to Pulse tokens while native `confirm()` calls were replaced by the shared accessible dialog and the empty malformed `public/js/frontend.jsphp` artifact was removed. That bridge enabled incremental delivery without claiming the domain hardening assigned to M3–M6; the completion audit below records its subsequent retirement.

## Completion audit

All registered administration routes and their Blade views were re-reviewed. Media, menus, the existing builder, themes and customizer, plugins and plugin settings, site settings, and SEO now use the shared Pulse feedback, card, button, error-summary, empty-state, responsive layout, and confirmation contracts. Administration templates contain no legacy `pulse-*` class consumers, and administration scripts contain no native `alert()` or `confirm()` calls. Route middleware remains the independent authorization boundary and shell navigation remains Gate-derived. Existing forms, CSRF tokens, storage behavior, builder schema, and module actions were preserved; this is presentation completion, not the domain hardening assigned to M3–M6.

Rendered-interface regression coverage exercises every remaining module family and rejects retired bridge classes and native dialogs. Browser/assistive-technology and representative viewport inspection could not be performed because no compatible browser runner is installed; TD-005 remains open.
