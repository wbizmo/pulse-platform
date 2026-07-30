# Administration shell

The Blade administration layout provides a skip link, sticky desktop sidebar, modal mobile drawer, top bar, authenticated-user menu, page content landmark, global toast live region, confirmation dialog, and progress status region. Navigation is grouped into Workspace, Access, and Platform and every module link is rendered through the canonical permission Gate. Routes independently retain permission, verification, active-account, and privileged-MFA middleware.

The drawer is hidden below 64rem until its labelled toggle opens it. Escape and the overlay close it, focus moves into it on open, and returns to the trigger on close. Desktop navigation reserves a stable width. Long navigation scrolls independently, allowing future authorized plugin contributions without covering content.

Page templates provide their title and contextual actions with `x-pulse.page-header`. The account menu always provides Profile and sessions, MFA and security, and a CSRF-protected sign-out form. No role name is used for navigation decisions.
