# PlgMyTailwind

**Tailwind CSS v4 Plugin for LifeTech OCMS Framework**

PlgMyTailwind provides **Tailwind CSS v4** integration for **LifeTech
OCMS**, allowing developers to use Tailwind utility classes when
building LifeTech OCMS themes, modules, plugins, components, layouts,
and views.

The plugin makes it easier to use Tailwind CSS within the LifeTechOCMS
ecosystem.

## Features

-   Tailwind CSS v4 integration
-   Designed specifically for LifeTechOCMS framework
-   Easy installation through the LifeTechOCMS backend
-   Supports LifeTech themes, modules, and plugins
-   Supports responsive Tailwind utility classes
-   Suitable for developing modern LifeTechOCMS user interfaces

## Installation

There are two recommended ways to install **PlgMyTailwind**.

### Option 1 --- LifeTech OCMS Marketplace

Visit the LifeTech OCMS Marketplace:

https://www.lifetech.host/hubs/community/products?product=plugin

Search for:

``` text
MyTailwind
```

Download the plugin.

Then:

1.  Log in to your **LifeTech OCMS Backend**.
2.  Go to **Packages**.
3.  Select **Plugins**.
4.  Install the downloaded **PlgMyTailwind** package.
5.  Enable or publish the plugin where required.

### Option 2 --- Install Directly from the Backend

LifeTech OCMS can also install supported plugins directly from its
online marketplace.

From your LifeTech OCMS Backend:

1.  Go to **Packages → Plugins**.
2.  Select **Browse Online**.
3.  Search for **MyTailwind**.
4.  Select the plugin.
5.  LifeTech OCMS will download and install the plugin automatically.

## Usage

Load the bundled script in your theme's head component or the page that needs Tailwind styling:

```php
<script src="<?= ltPluginPath() ?>/PlgMyTailwind/Services/tailwind_4_cdn.js"></script>
```

Then add **any** Tailwind utility classes to your HTML:

```html
<div class="rounded-xl bg-white p-6 shadow-md">
    <h1 class="text-2xl font-bold text-blue-600">Welcome to LifeTechOCMS</h1>
    <p class="mt-2 text-gray-600">Build your interface with Tailwind CSS utilities.</p>
    <button class="mt-4 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
        Get Started
    </button>
</div>
```
 
Tailwind classes can be used when developing:

-   Themes
-   Modules
-   Plugins
-   Components
-   Views
-   Layouts
-   Other supported LifeTech OCMS content


This allows LifeTech theme developers to build responsive interfaces
using Tailwind utility classes while keeping application functionality
within LifeTech OCMS.

## Plugin Information

``` text
Package Name: PlgMyTailwind
Package Type: Plugin 
Framework: LifeTechOCMS
Technology: Tailwind CSS
```

## Repository

https://github.com/ajayi-abolore/PlgMyTailwind

Clone the repository:

``` bash
git clone https://github.com/ajayi-abolore/PlgMyTailwind.git
```

## LifeTech OCMS Documentation

For information about developing themes, modules, plugins, components,
and other packages for LifeTech OCMS:

https://www.lifetech.host/hubs/Docs

## Compatibility

This repository contains the **Tailwind CSS v4** version of
PlgMyTailwind.

Always check the plugin package information and LifeTech OCMS version
requirements before installation.

## Author

**Ajayi Abolore A.**

LifeTech OCMS

https://www.lifetech.host

## License

This project is released under the **MIT License**.

See the `LICENSE` file included in this repository for details.
