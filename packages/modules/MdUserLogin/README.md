# MdUserLogin

**User authentication and account pages for LifeTechOCMS**

MdUserLogin is a LifeTechOCMS module that supplies ready-made views for signing in, registering an account, recovering a password, signing out, and displaying a user dashboard. It also includes an About Us page. The views use LifeTechOCMS site helpers and call existing user API endpoints; the archive **does not** include its own authentication controllers or models.

## Package information

| Property | Value |
| --- | --- |
| Package | `MdUserLogin` |
| Type | Module |
| Declared LifeTechOCMS version | `^2.7.4` |
| Declared dependency | `ThemeLifeTech` version `^1.2.1` |
| View technologies | HTML, JavaScript, Tailwind CSS |
| Additional styling | Font Awesome plugin |

## Features and pages

| View file | Exported route | Purpose |
| --- | --- | --- |
| `Login.html` | `/sign-in` | Sign in with credentials; optional remembered username |
| `Register.html` | `/sign-up` | Account registration |
| `ForgotPassword.html` | `/forgot-password` | Request a reset, verify an OTP, and submit a new password |
| `Logout.html` | `/logout` | Invoke the logout API and redirect |
| `UserDashboard.html` | `/MdUserLogin/UserDashboard` | User dashboard interface |
| `About-us.html` | `/about-us` | About/information page |

## Requirements

- A working LifeTechOCMS installation compatible with the package's declared framework version.
- The `ThemeLifeTech` dependency declared in `package.json`.
- LifeTechOCMS user endpoints and supporting JavaScript helpers available in the host installation.
- The `PlgMyTailwind` and `PlgMyFontAwesome` assets referenced by the views.

## Installation

1. Download or retain the **MdUserLogin ZIP package**.
2. Open your LifeTechOCMS backend and navigate to the module package installation interface.
3. Install the ZIP through LifeTechOCMS, rather than copying individual view files into an unrelated location.
4. Check that the declared `ThemeLifeTech` dependency is available.
5. Review the imported module content, pages, menu entries, and role permissions.
6. Publish or enable the module and its pages as appropriate for your installation.
7. Test `/sign-in`, `/sign-up`, `/forgot-password`, and `/logout` against your configured user API.

**Important:** Installation and publication behavior depends on the framework version and site configuration. The archive's `package.json` has `isPublished: null`, so do not assume that installation automatically publishes the module.

## How it works

The module provides frontend views that call LifeTechOCMS user endpoints:

| Operation | Endpoint referenced in views |
| --- | --- |
| Login | `POST /users/login` |
| Registration | `/users` |
| Request password reset | `/users/request-password-reset` |
| Verify reset OTP | `/users/verify-otp` |
| Reset password | `/users/reset-password` |
| Logout | `/users/logout` |

The exact HTTP method for each operation should be checked in the view source and against the installed API. The API base URL is supplied by the surrounding LifeTechOCMS environment; these are endpoint paths, not guaranteed complete public URLs.

## Package structure

```text
MdUserLogin/
├── Controllers/               # Empty in this export
├── Models/                    # Empty in this export
├── Services/
│   └── MdUserLoginRoutes.php  # Route class scaffold
├── Views/
│   ├── About-us.html
│   ├── ForgotPassword.html
│   ├── Login.html
│   ├── Logout.html
│   ├── Register.html
│   └── UserDashboard.html
├── ddm/                       # LifeTechOCMS content descriptors
├── Export/
│   ├── content.json
│   ├── menu.json
│   ├── page.json
│   ├── pageContent.json
│   └── table.json
├── Media/
├── LICENSE
├── package.json
└── README.md
```

## Customization

Edit the module's views through the LifeTechOCMS module content editor or your supported development workflow. The supplied views reference `ltSiteUrl()` for site links and `ltPluginPath()` for plugin assets. Review the form text, branding, styling, dashboard content, and post-login destination for your site.

If you change view files outside the backend, use the framework's supported file/content synchronization workflow so the installed content remains consistent.

## Known configuration and security considerations

Before using this module in production, review the following items found in the supplied archive:

- **Post-login redirect:** `Login.html` redirects to a URL containing `//user-dashboard`; the exported dashboard route is `/MdUserLogin/UserDashboard`. Align the redirect with the route you actually publish.
- **Token storage:** `Login.html` stores an authentication token in `localStorage`. Evaluate the XSS exposure and your application's authentication design; avoid exposing tokens to untrusted scripts.
- **Logout storage clearing:** `Logout.html` calls `localStorage.clear()`, which removes **all** local-storage keys for the origin, not only MdUserLogin keys. Prefer removing only the keys belonging to this application.
- **Password recovery:** Verify that OTP issuance, expiration, retry limits, and reset validation are enforced by the backend, not only the frontend.
- **Authorization:** The exported page permissions differ between anonymous and signed-in roles. Confirm that dashboard and logout access are protected by server-side checks.
- **Assets:** The views load Tailwind from `PlgMyTailwind` and Font Awesome from `PlgMyFontAwesome`; make sure the plugins are available and the paths resolve.
- **API requirements:** Confirm that the installed API supports the endpoints, request headers, and response fields expected by these views.

These are source-review observations, **not evidence that the module has been tested or that all flows currently work**.
