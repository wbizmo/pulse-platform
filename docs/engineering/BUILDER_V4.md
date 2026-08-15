# Builder V4 contract

Builder V4 is a first-party, server-authoritative document contract. A document has exactly `schema_version` (currently `1`) and an ordered `nodes` list. Every node has exactly a UUID `id`, registered `type`, validated `props`, allow-listed responsive `settings`, and ordered `children`. IDs survive reorder; browser duplicate and template insertion recursively create new IDs.

## Registry and limits

`BlockRegistry` is the single catalogue/editor-metadata source for `section`, `columns`, `hero`, `text`, `image`, `video`, `cta`, `features`, `stats`, `accordion`, and `testimonial`. Only section/columns are containers. Sections cannot nest inside another container. Documents are limited to 128 KiB, 100 nodes, depth 4, and 24 children per list. Collection blocks allow at most 12 items and every string has an explicit per-property bound. Responsive values are tokens for alignment, spacing, width and breakpoint visibility—not CSS or arbitrary classes.

Raw Custom HTML was removed. Legacy unversioned data is neither rewritten nor rendered, so historical HTML cannot become executable; Builder surfaces a recovery warning before intentional replacement. Video stores a validated HTTPS YouTube/Vimeo URL and renders a safe outbound link rather than administrator-supplied embed markup. Images store managed-media IDs and media deletion scans Builder dependencies.

## Persistence and reuse

`SaveBuilder` uses the Page lifecycle `lock_version` as one coherent compare-and-swap version. A stale update changes nothing and returns a reconciliation error. Successful transactions increment the version and append a metadata-only audit containing page, schema, node count and versions; full content is not audited.

Reusable templates are persisted versioned documents with a stable UUID and bounded name/content. Creation validates the whole fragment. Insertion has **snapshot semantics**: the editor recursively copies nodes with new IDs, so later template update/deletion cannot alter existing pages and no live usage dependency is implied.

The editor stores at most one 128-KiB local recovery draft under a page-and-loaded-version key, offers restore/discard only for that exact version, clears it after save, and uses the browser `beforeunload` contract while dirty. Public rendering and signed authenticated preview use the same validated resolver. Unsupported or malformed stored data produces no Builder output; public lifecycle and private/no-store/noindex preview rules remain owned by the Page domain.
