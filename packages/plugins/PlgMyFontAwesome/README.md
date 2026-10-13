# PlgMyFontAwesome

**Font Awesome Plugin for LifeTech OCMS**

PlgMyFontAwesome integrates **Font Awesome** with **LifeTech OCMS**,
making Font Awesome icons and styles available to LifeTech OCMS themes,
modules, plugins, components, layouts, views, and other supported
content.

The plugin provides the required Font Awesome CSS and webfont assets
locally, allowing developers to use icons in LifeTech OCMS interfaces
without depending on an external Font Awesome CDN.

## Features

-   Font Awesome integration for LifeTech OCMS
-   Local CSS and webfont assets
-   No external Font Awesome CDN required for normal plugin usage
-   Easy installation through the LifeTech OCMS backend
-   Can be used by LifeTech themes, modules, plugins, and views
-   Supports Font Awesome utility and icon classes
-   Suitable for backend and frontend interface development

## Installation

There are two recommended ways to install **PlgMyFontAwesome**.

### Option 1 --- LifeTech OCMS Marketplace

Visit the LifeTech OCMS Marketplace:

https://www.lifetech.host/hubs/community/products?product=plugin

Search for:

``` text
PlgMyFontAwesome
```

Download the plugin package.

Then:

1.  Log in to your **LifeTech OCMS Backend**.
2.  Go to **Packages**.
3.  Select **Plugins**.
4.  Install the downloaded **PlgMyFontAwesome** package.
5.  Enable or publish the plugin where required.

### Option 2 --- Install Directly from the Backend

LifeTech OCMS can also download and install supported plugins directly
from its online marketplace.

From your LifeTech OCMS Backend:

1.  Go to **Packages → Plugins**.
2.  Select **Browse Online**.
3.  Search for **PlgMyFontAwesome**.
4.  Select the plugin.
5.  LifeTech OCMS will automatically download and install the plugin.

## Loading Font Awesome

After installing **PlgMyFontAwesome**, the plugin stylesheet can be
loaded using the LifeTech OCMS plugin path helper:

``` php
<link
    rel="stylesheet"
    href="<?= ltPluginPath() ?>/PlgMyFontAwesome/Services/font-awesome.css"
/>
```

Using `ltPluginPath()` allows LifeTech OCMS to resolve the plugin path
for the current installation.

## Usage

Once the stylesheet is loaded, Font Awesome classes can be used in your
LifeTech OCMS HTML, PHP views, components, layouts, modules, plugins,
and themes.

For example:

``` html
<i class="fa-solid fa-house"></i>
```

You can also use icons inside buttons and interface elements:

``` html
<button type="button">
    <i class="fa-solid fa-save"></i>
    Save
</button>
```

Another example:

``` html
<a href="#">
    <i class="fa-solid fa-user"></i>
    My Account
</a>
```

## Using with LifeTech OCMS Themes

PlgMyFontAwesome can be loaded by a LifeTech OCMS theme and then used
throughout its components, layouts, and views.

Example:

``` php
<link
    rel="stylesheet"
    href="<?= ltPluginPath() ?>/PlgMyFontAwesome/Services/font-awesome.css"
/>
```

Then use the required icon classes:

``` html
<nav>
    <a href="#">
        <i class="fa-solid fa-house"></i>
        Home
    </a>

    <a href="#">
        <i class="fa-solid fa-user"></i>
        Account
    </a>

    <a href="#">
        <i class="fa-solid fa-gear"></i>
        Settings
    </a>
</nav>
```

## Local Assets

PlgMyFontAwesome includes the assets required to provide Font Awesome
from within the LifeTech OCMS installation.

The repository includes directories such as:

``` text
PlgMyFontAwesome/
├── Controllers/
├── Export/
├── Media/
├── Models/
├── Services/
├── Views/
├── ace/
├── css/
├── ddm/
├── webfonts/
├── index.php
├── package.json
├── README.md
└── LICENSE
```

The `webfonts` directory contains the font resources used by the Font
Awesome stylesheets.

## Plugin Information

``` text
Package Name: PlgMyFontAwesome
Package Type: Plugin
Plugin Version: 2.0
Framework: LifeTech OCMS
Language/Technology: CSS
```

## Repository

GitHub:

https://github.com/ajayi-abolore/PlgMyFontAwesome

Clone the repository:

``` bash
git clone https://github.com/ajayi-abolore/PlgMyFontAwesome.git
```

Then enter the repository:

``` bash
cd PlgMyFontAwesome
```

## LifeTech OCMS Documentation

For information about developing themes, modules, plugins, components,
and other packages for LifeTech OCMS, see:

https://www.lifetech.host/hubs/Docs

## Compatibility

This repository currently contains **PlgMyFontAwesome v2.0**.

Always check the plugin package information and LifeTech OCMS version
requirements before installation.

## Author

**Ajayi Abolore A.**

LifeTech OCMS

https://www.lifetech.host

## License

This project is released under the **MIT License**.

See the `LICENSE` file included in this repository for details.
