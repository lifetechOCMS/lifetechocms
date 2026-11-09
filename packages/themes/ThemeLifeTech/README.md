# ThemeLifeTech

**The default frontend theme for LifeTech OCMS.**

[Repository](https://github.com/lifetechOCMS/ThemeLifeTech) · [Theme development documentation](https://www.lifetech.host/hubs/Docs#theme-development-overview) · [Report an issue](https://github.com/lifetechOCMS/lifetechocms/issues)

ThemeLifeTech is a responsive frontend theme for LifeTech OCMS, providing a reusable site layout, navigation, page views, and authentication screens. It demonstrates how LifeTech theme components, views, assets, and exported content work together.

## Package information

| Property | Value |
| --- | --- |
| Package | `ThemeLifeTech` |
| Type | `theme` |
| Version in `package.json` | `1.2.1` |
| Declared LifeTech version | `2.7.4` |
| Technologies | HTML, Tailwind CSS, JavaScript, PHP templates |
| Default repository branch | `v1.x` |
| License | MIT |

> The version and compatibility values above are taken from the repository's `package.json`. Confirm compatibility with your installed LifeTech OCMS version before installing.

## Features

- Responsive frontend structure with header/navigation, dynamic body content, and footer.
- Navigation populated from LifeTech's current-role menu endpoint.
- Home/marketing sections, including hero, features, services, team, and about content.
- Login, logout, registration, and forgotten-password views.
- Tailwind CSS styling and Font Awesome integration via LifeTech plugins.
- Exported page, menu, content, and table definitions included with the theme.

## Repository structure

```text
ThemeLifeTech/
├── Assets/
│   └── js/
│       └── Script.js
├── Components/
│   ├── ContentLoadBody.html
│   ├── Footer.html
│   ├── Layout.html
│   └── Nav-bar.html
├── Layouts/
├── Views/
│   ├── ForgotPassword.html
│   ├── Lfth-about-us.html
│   ├── Lfth-api-doc.html
│   ├── Lfth-features.html
│   ├── Lfth-forum.html
│   ├── Lfth-hero.html
│   ├── Lfth-services.html
│   ├── Lfth-team.html
│   ├── Lfth-why-us.html
│   ├── Login.html
│   ├── Logout.html
│   └── Register.html
├── Export/
│   ├── content.json
│   ├── menu.json
│   ├── page.json
│   ├── pageContent.json
│   └── table.json
├── ddm/
│   └── *.Component.lifetech / *.View.lifetech / *.Asset.lifetech
├── index.php
├── package.json
├── LICENSE
└── README.md
```

Some folders also contain `index.php` files. The `Layouts/` directory currently contains an `index.php` placeholder; the theme's main layout markup is in `Components/Layout.html`.

## Requirements

- A working LifeTech OCMS installation compatible with the package metadata.
- The **PlgMyTailwind** plugin, used by `Components/Layout.html` to load `tailwind_4_cdn.js`.
- The **PlgMyFontAwesome** plugin, used by `Components/Layout.html` to load `font-awesome.css`.

The layout references these plugin resources:

```php
<script src="<?= ltPluginPath() ?>/PlgMyTailwind/Services/tailwind_4_cdn.js"></script>
<link rel="stylesheet" href="<?= ltPluginPath() ?>/PlgMyFontAwesome/Services/font-awesome.css">
```

Install the required plugins before expecting the complete styling and icons to work.

## Installation

### Option 1 — Download and install through the backend

1. Open the [ThemeLifeTech repository](https://github.com/lifetechOCMS/ThemeLifeTech) and download the package ZIP, or obtain the published theme package from the LifeTech marketplace when available.
2. Sign in to your LifeTech OCMS backend.
3. Open **Packages → Themes** and use the theme installation/upload option.
4. Select the package and complete installation.
5. Enable or assign the theme through the backend theme-management controls as appropriate.
6. Confirm that its dependent plugins are installed, then test the frontend pages.

### Option 2 — Browse online from LifeTech OCMS

If your LifeTech OCMS backend provides the **Browse Online** option for themes, open **Packages → Themes**, find **ThemeLifeTech**, and install it from there. Availability depends on whether the theme is published in the marketplace.

> Installing a raw GitHub source ZIP may differ from installing a marketplace export. Use the backend's supported theme package format and consult the documentation for your LifeTech version.

## How the theme works

### Layout and content

`Components/Layout.html` defines the document head, theme styles, and outer page structure. `Components/ContentLoadBody.html` supplies the content area and invokes LifeTech's dynamic content loader:

```php
<main class="mt-12 bg-gray-100">
    <?php dddm::loadDynamicContent(); ?>
</main>
```

The loader renders content assigned to the current page, including content provided by themes, modules, and plugins.

### Navigation

`Components/Nav-bar.html` renders desktop and mobile menus. Its JavaScript requests the role-aware navigation endpoint:

```javascript
const response = await fetch(`${BASE_URL}/menus/is-navigation`, {
    headers: { Accept: 'application/json' }
});
const { responseData } = await response.json();
```

The menu renderer uses returned items and their children to build navigation links. Configure menu visibility and permissions through LifeTech OCMS.

### Assets and plugin resources

`Assets/js/Script.js` contains theme-side JavaScript. The navigation component imports the asset using LifeTech's content import helper:

```php
<?php ltImport('ThemeLifeTech', 'Script.js') ?>
```

The layout also uses `ltSiteHostAddress()`, `ltSiteLogo()`, and `ltPluginPath()` to resolve site branding and plugin assets.

### Views

The `Views/` folder provides frontend sections and authentication screens. Published views can be assigned to pages through the LifeTech backend, where the dynamic content loader renders them within the active layout.

### Export and DDM

`Export/` contains JSON files for content, menus, pages, page-content assignments, and tables. The `ddm/` directory contains `.lifetech` definitions corresponding to theme components, views, and an asset. These are package resources; do not manually import or edit them in production unless following a documented LifeTech workflow.

## Customisation

To adapt ThemeLifeTech for a site:

1. Update branding and navigation using the LifeTech backend where supported.
2. Edit theme components for structural changes and views for page-specific content.
3. Adjust Tailwind utility classes or the theme styles in `Components/Layout.html`.
4. Put reusable JavaScript in `Assets/` and load it through the appropriate LifeTech helper.
5. Test both mobile and desktop navigation, authentication pages, and dynamic content rendering.
6. Follow the LifeTech theme-development conventions when preparing a new version or distributing a derived theme.

For a separate theme, use a distinct PascalCase package name and keep its `package.json` metadata consistent with the package contents.

## Documentation and support

- [LifeTech OCMS documentation](https://www.lifetech.host/hubs/Docs)
- [Theme development overview](https://www.lifetech.host/hubs/Docs#theme-development-overview)
- [ThemeStarter practical example](https://github.com/ajayi-abolore/ThemeStarter)
- [ThemeLifeTech repository](https://github.com/lifetechOCMS/ThemeLifeTech)
- [Report bugs and observations](https://github.com/lifetechOCMS/lifetechocms/issues)

When reporting an issue, include the LifeTech version, ThemeLifeTech version, reproduction steps, and any relevant browser or server error messages.

## License

ThemeLifeTech is released under the [MIT License](LICENSE). Copyright © LifeTech OCMS contributors; see the repository license for its full terms.
