# Pulse design system

## Architecture

Pulse administration uses local, deterministic Vite assets and the system UI font stack. `resources/css/app.css` defines three token layers: `--p-gray-*`, type, and spacing primitives; semantic surface, text, border, action, and feedback tokens; then component contracts such as control height, button radius, and card padding. Views must consume semantic or component tokens rather than repeat arbitrary values. Primitive tokens describe values, not usage.

The type scale is compact for long administrative sessions. The four-point spacing scale, restrained radii, one subtle elevation, and blue action color form the product language. Success, warning, error, and information always combine text with color. Focus uses a high-visibility ring. Layer tokens reserve sidebar, overlay, dialog, and toast ordering.

Responsive CSS uses small (36rem), medium (48rem), and large (64rem) token values as documented contracts. Custom properties cannot currently be used directly in media conditions, so matching values occur once in each media query. Motion is short and functional, and the reduced-motion query effectively removes transitions and animation.

## Naming and use

* Primitive: `--p-{family}-{step}`, such as `--p-space-4`.
* Semantic: `--p-{purpose}`, such as `--p-surface-raised`.
* Component: `--p-{component}-{property}`, such as `--p-control-height`.
* Blade/CSS component classes use `p-`; variants use `p-component--variant`.

New components should first reuse existing semantic tokens. Add a primitive only when it is broadly reusable, then expose a semantic alias. Runtime font, icon, CSS, or JavaScript CDNs are prohibited for essential administration UI.

## Legacy module bridge

Existing module templates retained during incremental hardening consume a bounded `pulse-*` presentation bridge in `resources/css/app.css`. The bridge is built exclusively from Pulse tokens and supplies responsive cards, grids, controls, tables, actions, status, and builder regions. New templates use `p-*` components directly; each owning vertical removes its bridged selectors as it migrates rather than duplicating the bridge.
