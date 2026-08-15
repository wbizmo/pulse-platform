# Recovery checkpoint

- **Completed milestone:** M3 media security, referential integrity, and processing is complete on `work`, following taxonomy merge `9cbb45d`.
- **Delivered boundaries:** Managed JPEG/PNG/WebP/GIF records; configured byte/pixel/dimension bounds; server-decoded metadata; opaque public-disk paths; SVG/executable rejection; persistence cleanup; audited create/update/delete; paginated/eager media administration; Pulse metadata forms/dialogs; restrictive page/post featured-media foreign keys; bounded editor selection; public rendering; and dependency-aware deletion.
- **Legacy strategy:** Existing post URL values were not fabricated into records or discarded. They remain in inactive `legacy_featured_image` archival storage for an operator-led conversion/disposal decision; no runtime form or renderer consumes them.
- **Preserved boundaries:** M1 identity/MFA/RBAC/session/audit, M2 Pulse UI/native-dialog prohibition, and M3 lifecycle/taxonomy publication, locking, preview, assignment, and archive behavior remain intact. Builder revisions/hierarchy remain M4.
- **Next exact milestone:** Continue M3 with menus, then SEO and forms.
- **Unresolved risks:** TD-005 browser/assistive-technology testing remains environment-blocked. TD-007 retains only legacy-data cleanup plus M4 hierarchy/revision work. MySQL migration/storage integration remains a release-environment gate; SQLite migration coverage passed.
- **Verification:** Focused media suite: 6 tests/32 assertions. Complete suite: 54 tests/366 assertions. Baseline, migration, build, diff, and smoke commands are recorded after execution below.
- **Commit:** The focused media commit is the current `work` tip; obtain its immutable SHA with `git rev-parse HEAD` (a commit cannot truthfully embed its own hash).
