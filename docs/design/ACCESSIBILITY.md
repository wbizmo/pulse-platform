# Administration accessibility

The implemented foundation targets WCAG 2.2 AA: semantic navigation and main landmarks, a keyboard-visible skip link, logical page headings, labelled controls, inline errors plus an error summary, visible focus, 42px controls, non-color status text, responsive tables, live-region toasts, and native-dialog semantics. Drawer and confirmation controls support Escape and focus restoration. Reduced-motion preferences suppress nonessential animation.

Responsive table cells carry their column labels at small widths. Toast content is escaped by Blade on the server and assigned with `textContent` in JavaScript. Dialog title and consequence text are also assigned with `textContent`. Content, media, navigation, and builder destructive actions use the same custom dialog without native browser prompts. Destructive actions remain ordinary CSRF-protected forms; the dialog only confirms intent and never replaces server authorization.

Browser/assistive-technology verification remains required before release. Automated rendered-markup tests cover the principal structural contracts but do not establish full WCAG conformance.
