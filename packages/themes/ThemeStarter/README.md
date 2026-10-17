# ThemeStarter v1.2.1 — automatic default layout test

This sample targets the updated LifeTechOCMS behaviour described by the platform author: Layouts/Default.html takes priority as the theme default layout; otherwise components marked Is Theme Default provide the fallback composition.

## Install and preview
1. Use a test installation running the new backend update.
2. In Packages → Themes Development choose Install Theme and upload this ZIP. If ThemeStarter already exists, back it up and use your supported package update/replacement workflow. Do not assume an existing custom page layout assignment will be cleared by import.
3. Preview Home and About through LifeTechOCMS. Do not manually assign default components first: automatic layout selection is what this package tests.
4. Confirm /ThemeStarter/Home shows the WelcomeBanner and the welcome button works.
5. Confirm /ThemeStarter/About shows AboutHighlights, without the WelcomeBanner.
6. Both pages should show one header, one footer and one copy of their assigned view.
7. Check page access/content publication if the backend requires it. Record any manual action still needed as a test observation.

## Composition
Default.html imports TopHeader and Header, calls dddm::loadDynamicContent once inside main, then imports Footer. It does not import ContentLoadBody because it already supplies the loader. TopHeader links the shared CSS with ltThemePath; Footer links plain JavaScript the same way.

All component Is Theme Default flags are No in this package. This deliberately isolates Default.html selection from the fallback component mechanism and avoids duplicate wrappers. Default.html is registered as Layout content. No separate page-layout assignment is encoded for Home or About.

WelcomeBanner.html and AboutHighlights.html are nondefault components explicitly imported by Home and About respectively. Neither should appear on the other page.

## Optional documentation extensions
DynamicHeader.html provides the role-filtered menu example. The basic layout uses Header.html so an API issue cannot prevent testing automatic layout selection. To test dynamic navigation, replace the Header import in Default.html with DynamicHeader.

MemberLayout, MemberSidebar, MemberHeader and MemberDashboard remain optional examples. Assign MemberLayout only when testing that custom layout; otherwise MemberDashboard can render through Default.html. Review member page access roles separately.

Posts.html is the optional blog API example and requires the matching backend blog endpoint. Home/About and the layout test require no module or Tailwind plugin.

## Expected test results
| Page | Layout | Special component |
| --- | --- | --- |
| Home | Default.html automatically | WelcomeBanner.html |
| About | Default.html automatically | AboutHighlights.html |
| MemberDashboard without custom assignment | Default.html automatically | None |
| Posts | Default.html automatically | None; API result or error state |

Check a narrow screen, a subfolder installation, CSS/JS requests, the Home button, and one dynamic loader. Imported layouts and components must remain registered with the exact case-sensitive names.

## Import metadata and distribution
The included metadata follows the supplied ThemeBlog export structure, extended with registered Default and special components. This archive was assembled from source, not exported by the updated running backend. Import reconstruction and the new layout behaviour require your live test; source checks cannot certify them. Export the successful result through LifeTechOCMS before sharing a verified installer.

Version: v1.2.1. No specific core compatibility claim is encoded. The earlier v1.0.0 documentation example used default components; this release tests the new Default.html priority rule.


## Beginner site-helper examples — v1.2.1
Frontend PHP echoes helper values directly, with htmlspecialchars for HTML output. No logo-path resolution, social URL parsing, custom helper wrappers, or conditional setting logic is included.

| Location | Helpers demonstrated |
| --- | --- |
| TopHeader | ltPageTitle, ltSiteFaviconUrl, ltThemePath |
| Header | ltSiteLogoUrl, ltSiteName, ltSiteTitle, ltSiteAlias, ltSiteUrl |
| Contact | ltSiteEmail, ltSiteEmail2, ltSitePhone, ltSitePhone2, ltSiteAddress, ltSiteAddress2, ltSiteOpenHour, ltSiteClosedHour |
| Footer | ltSiteCopyright, ltSiteLinkedln, ltSiteInstagram, ltSiteFacebook, ltSiteTwitter, ltSiteYoutube, ltThemePath |

AppCore supplies resolved logo/favicon URLs and the page title. Configure the demonstrated site settings before previewing; blank values remain blank. The social labels match their corresponding helpers. Database/environment helpers are not displayed to visitors.

The package uses ltVersion and contains no explicit layout-mapping fields. Default.html remains the automatic default layout; component default flags remain No. Special components remain limited to Home and About. MemberLayout is still an optional separately assigned example. CSS and JavaScript use direct theme asset links.

The earlier Default.html import test succeeded according to the platform author. This updated sample needs a live regression preview before public upload. Check the page title, logo, favicon, contact details, social destinations, shared assets, and both special components.

## Advanced dynamic-menu demo — v1.2.1
After importing, assign DynamicMenuDemo.html to DynamicMenuLayout.html through your backend layout controls. No mapping fields have been added to package.json. Until assigned, the view will use Default.html and static navigation; that is expected.

| Layout | Header | Intended views |
| --- | --- | --- |
| Default.html | Header.html (static) | Home, About, Contact and ordinary views |
| MemberLayout.html | MemberHeader plus sidebar | MemberDashboard, when manually assigned |
| DynamicMenuLayout.html | DynamicHeader.html (API) | DynamicMenuDemo, when manually assigned |

DynamicHeader requests /menus/is-navigation without a package filter, so the backend may return permitted menus across installed packages. Its loader still supports an optional packageName parameter if a developer chooses to filter. The demo uses the current session and does not manufacture a token or role IDs. Cross-origin API authentication needs the platform's supported configuration.

The demo reuses TopHeader, Footer and shared assets, with one dynamic content loader. Do not separately publish the static Header on this custom-layout page. Test as Anonymous and as a signed-in user, nested links, new-tab targets, empty data and a failed request. Edit menu names/navigation publication in the backend to control what is returned.

Static code checks and metadata references passed; the new custom-layout assignment and API response still require your live test. This does not implement automatic custom-layout mapping.

## Navigation URL fix — v1.2.1
DynamicHeader now uses ltSiteUrl() directly as the base for internal menu links. JavaScript trims joining slashes and appends routePath; no URL constructor is used for menu destinations. External routes and # placeholders are returned unchanged. This preserves AppCore's installation subfolder.

After updating, keep DynamicMenuDemo assigned to DynamicMenuLayout. Refresh the preview and confirm returned menus render and internal links retain the installation subfolder.
