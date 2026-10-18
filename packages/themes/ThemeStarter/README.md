# ThemeStarter — LifeTech OCMS Starter Theme

**Version:** `v1.2.1` · **Package:** `ThemeStarter` · **Platform:** LifeTech OCMS

ThemeStarter is a beginner-friendly theme package for learning how LifeTech OCMS handles **layouts, components, views, assets, site helpers, and dynamic content**. Its primary example tests automatic use of `Layouts/Default.html`, while optional examples demonstrate custom layouts, role-aware navigation, and backend API integration.

> **Testing status:** The repository documents source and metadata checks, but some import behaviour, custom layout assignments, and live API responses must still be verified in an updated LifeTech OCMS installation. Do not treat the source package as a fully validated marketplace export until tested.

## Contents

1. [What this theme demonstrates](#1-what-this-theme-demonstrates)
2. [Package structure](#2-package-structure)
3. [Installation](#3-installation)
4. [How the default layout works](#4-how-the-default-layout-works)
5. [Pages and reusable components](#5-pages-and-reusable-components)
6. [Site information helpers](#6-site-information-helpers)
7. [Custom layouts and dynamic navigation](#7-custom-layouts-and-dynamic-navigation)
8. [Optional blog API example](#8-optional-blog-api-example)
9. [Testing checklist](#9-testing-checklist)
10. [Troubleshooting](#10-troubleshooting)
11. [Documentation and licensing](#11-documentation-and-licensing)

---

## 1. What this theme demonstrates

- **Automatic default layout:** `Layouts/Default.html` is intended to wrap pages without manually assigning a layout.
- **Reusable components:** shared header, top header, and footer.
- **Page-specific components:** `WelcomeBanner.html` for Home and `AboutHighlights.html` for About.
- **Views:** content for pages such as Home, About, and optional examples.
- **Assets:** shared CSS and JavaScript referenced using `ltThemePath()`.
- **Site helpers:** PHP functions for configured site identity, contact details, and social links.
- **Alternative layouts:** optional member and dynamic-menu layouts that are assigned to specific views through the backend.
- **Dynamic menus and API integration:** optional demonstrations that depend on matching backend features.

The base Home/About example does **not** require the optional blog module or Tailwind plugin.

[↑ Back to contents](#contents)

## 2. Package structure

The repository contains these top-level directories and files:

```text
ThemeStarter/
├── Assets/
├── Components/
├── Export/
├── Layouts/
├── Views/
├── ddm/
├── LICENSE
├── README.md
├── index.php
└── package.json
```

| Location | Purpose |
| --- | --- |
| `Layouts/` | Default and optional alternative page compositions |
| `Components/` | Reusable page elements, including shared and page-specific components |
| `Views/` | Page content and optional demonstrations |
| `Assets/` | Theme stylesheets and JavaScript |
| `Export/` | Package export-related files |
| `ddm/` | Framework-related theme package files |
| `package.json` | Package metadata used by the theme installation workflow |

Keep **exact filename case** when referencing layouts, views, and components.

[↑ Back to contents](#contents)

## 3. Installation

### Install from the theme package

1. Use a **test installation** of LifeTech OCMS with the updated theme layout behaviour.
2. Obtain the ThemeStarter package from the [GitHub repository](https://github.com/ajayi-abolore/ThemeStarter). For an installable archive, use the package ZIP intended for your LifeTech backend rather than assuming any arbitrary GitHub source ZIP has been validated for import.
3. Sign in to your LifeTech OCMS backend.
4. Open **Packages → Themes Development**, select **Install Theme**, and upload the supported package ZIP.
5. If ThemeStarter already exists, back it up and use the backend's supported update or replacement process.
6. Open the Home and About pages and confirm that the default layout is applied automatically.

> Existing page-specific layout assignments might remain in place after an import. Do not assume the installer removes them.

### Initial preview

The repository documents these example routes:

```text
/ThemeStarter/Home
/ThemeStarter/About
```

If LifeTech OCMS is installed in a subfolder, use the site's actual base URL before these routes.

[↑ Back to contents](#contents)

## 4. How the default layout works

The theme's `Layouts/Default.html` is intended to act as the automatic page layout. It imports shared components and calls the dynamic content loader **once** in the main content region.

**Illustrative composition** (not a verbatim copy of the repository file):

```php
<?php // Import TopHeader and Header using the supported theme import mechanism. ?>

<main class="container">
    <?php dddm::loadDynamicContent(); ?>
</main>

<?php // Import Footer using the supported theme import mechanism. ?>
```

In the repository's current design:

- `Default.html` takes priority as the theme's default layout.
- All components have **Is Theme Default = No** to isolate and test the `Default.html` mechanism.
- `Default.html` already calls `dddm::loadDynamicContent()`, so it should **not** also import a `ContentLoadBody` component that calls the loader again.
- Home and About do not have separate custom layout assignments encoded in package metadata.
- If the theme has no automatic `Default.html` layout, the framework's default-component mechanism may provide a fallback, depending on the installed release.

Avoid rendering two headers, two footers, or duplicate view content.

[↑ Back to contents](#contents)

## 5. Pages and reusable components

| Page or view | Intended layout | Special component |
| --- | --- | --- |
| Home | `Default.html` automatically | `WelcomeBanner.html` |
| About | `Default.html` automatically | `AboutHighlights.html` |
| Contact | `Default.html` automatically | Contact information helpers |
| MemberDashboard, without a custom assignment | `Default.html` automatically | None required |
| Posts | `Default.html` automatically | Optional blog API example |

`WelcomeBanner.html` should appear only on Home, and `AboutHighlights.html` only on About. The shared header and footer should appear once per page.

### Shared assets

ThemeStarter links shared CSS and JavaScript from theme assets using `ltThemePath()`. For example, the helper can be used in an asset URL:

```php
<link rel="stylesheet" href="<?= ltThemePath() ?>/Assets/your-stylesheet.css">
```

Replace the illustrative asset filename with the **actual** filename in your package; do not assume the example exists as written.

[↑ Back to contents](#contents)

## 6. Site information helpers

ThemeStarter demonstrates direct PHP output of LifeTech site settings. The repository associates these helpers with the following components:

| Component | Helpers demonstrated |
| --- | --- |
| TopHeader | `ltPageTitle`, `ltSiteFaviconUrl`, `ltThemePath` |
| Header | `ltSiteLogoUrl`, `ltSiteName`, `ltSiteTitle`, `ltSiteAlias`, `ltSiteUrl` |
| Contact | `ltSiteEmail`, `ltSiteEmail2`, `ltSitePhone`, `ltSitePhone2`, `ltSiteAddress`, `ltSiteAddress2`, `ltSiteOpenHour`, `ltSiteClosedHour` |
| Footer | `ltSiteCopyright`, `ltSiteLinkedln`, `ltSiteInstagram`, `ltSiteFacebook`, `ltSiteTwitter`, `ltSiteYoutube`, `ltThemePath` |

Example of safely escaping a helper's text value in HTML:

```php
<h1><?= htmlspecialchars(ltSiteName(), ENT_QUOTES, 'UTF-8') ?></h1>
```

The theme expects the site settings to be configured in the backend. Unset values may appear blank. The repository uses the helper spelling **`ltSiteLinkedln`**; check the helper available in your installed framework before changing it.

[↑ Back to contents](#contents)

## 7. Custom layouts and dynamic navigation

The repository includes optional layouts that are **not automatically assigned** through `package.json`.

| Layout | Navigation or structure | Intended view |
| --- | --- | --- |
| `Default.html` | `Header.html` (static navigation) | Home, About, Contact, ordinary pages |
| `MemberLayout.html` | Member header and sidebar | MemberDashboard, when assigned manually |
| `DynamicMenuLayout.html` | `DynamicHeader.html` (API-driven navigation) | DynamicMenuDemo, when assigned manually |

### Try the dynamic menu example

1. Import the theme into a compatible test installation.
2. In the backend's layout assignment controls, assign `DynamicMenuDemo.html` to `DynamicMenuLayout.html`.
3. Open the page as an anonymous visitor and as an authenticated user.
4. Verify menu visibility, nested links, new-tab targets, empty responses, and API error handling.

`DynamicHeader.html` requests:

```http
GET /menus/is-navigation
Accept: application/json
```

The endpoint returns navigation permitted for the current user's role(s). The demonstration does not invent a bearer token or role ID; authenticated cross-origin setups require the platform's supported configuration.

For internal links, the dynamic menu example uses `ltSiteUrl()` as the base so installations in subfolders retain their path. External links and `#` placeholders remain unchanged.

> Until you assign `DynamicMenuDemo.html` to `DynamicMenuLayout.html`, the view is expected to use the automatic `Default.html` layout and static navigation.

[↑ Back to contents](#contents)

## 8. Optional blog API example

`Posts.html` is an optional example for connecting a theme view to a backend blog endpoint. It requires a compatible blog module or API endpoint to be available. The base layout and Home/About examples work without it.

When testing Posts, verify both successful data rendering and the visible error or empty state if the backend API is unavailable.

[↑ Back to contents](#contents)

## 9. Testing checklist

- [ ] Install or update the theme in a non-production LifeTech OCMS instance.
- [ ] Confirm `Default.html` is selected automatically for Home and About.
- [ ] Confirm Home displays `WelcomeBanner.html` only.
- [ ] Confirm About displays `AboutHighlights.html` only.
- [ ] Confirm each page renders one header, one footer, and one dynamic content load.
- [ ] Verify CSS and JavaScript requests load successfully.
- [ ] Verify page title, logo, favicon, contact information, and social links.
- [ ] Check a narrow/mobile viewport.
- [ ] Test a LifeTech OCMS installation under a subfolder.
- [ ] Test the optional MemberLayout assignment separately.
- [ ] Test the optional DynamicMenuLayout assignment and role-filtered API response.
- [ ] Test Posts only when its backend endpoint is installed.
- [ ] Export the successful installation through LifeTech OCMS before distributing a verified marketplace package.

[↑ Back to contents](#contents)

## 10. Troubleshooting

| Symptom | What to check |
| --- | --- |
| Layout does not appear | Confirm the updated backend supports automatic `Default.html` selection and that the layout was registered during import. |
| Duplicate content | Ensure `dddm::loadDynamicContent()` is called only once in the active layout composition. |
| Wrong component on a page | Check view imports and page-specific component assignments. |
| Missing CSS or JavaScript | Check actual asset filenames, `ltThemePath()`, browser network requests, and installation subfolder. |
| Dynamic menu is empty | Verify the endpoint, published menu items, current roles, and authentication configuration. |
| Custom layout is not used | Assign the intended view to that layout through the backend. |
| Import fails | Inspect the backend installation error and logs; source-code checks alone cannot confirm installer compatibility. |

[↑ Back to contents](#contents)

## 11. Documentation and licensing

- **Theme source:** [ajayi-abolore/ThemeStarter](https://github.com/ajayi-abolore/ThemeStarter)
- **LifeTech OCMS documentation:** [Documentation portal](https://www.lifetech.host/hubs/Docs)
- **Theme development documentation:** [LifeTech OCMS docs repository](https://github.com/lifetechOCMS/docs/tree/master/theme-dev)
- **License:** See the repository's [LICENSE](LICENSE) file (MIT).

**Version note:** This README documents the repository's stated `v1.2.1` example. It does not claim compatibility with a particular LifeTech OCMS core release because the repository does not encode a verified core-version compatibility requirement.

---

[↑ Back to contents](#contents) · [LifeTech OCMS documentation](https://www.lifetech.host/hubs/Docs)
