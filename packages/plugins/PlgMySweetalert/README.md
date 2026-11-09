# PlgMySweetalert

**SweetAlert Plugin for LifeTech OCMS**

PlgMySweetalert integrates **SweetAlert2** with **LifeTech OCMS**,
allowing developers to create modern alert dialogs, confirmation
messages, success notifications, warnings, errors, and interactive
popups within LifeTech OCMS applications.

## Features

-   SweetAlert2 integration for LifeTech OCMS
-   Success, error, warning, info, and question dialogs
-   Confirmation dialogs for important actions
-   Custom titles, messages, buttons, and icons
-   Responsive popup dialogs
-   Designed for LifeTech OCMS themes, modules, plugins, views, and
    layouts
-   Easy installation through the LifeTech OCMS backend

## Installation

There are two recommended ways to install **PlgMySweetalert**.

### Option 1 --- LifeTech OCMS Marketplace

Visit the LifeTech OCMS Marketplace:

https://www.lifetech.host/hubs/community/products?product=plugin

Search for:

``` text
SweetAlert
```

Download the plugin package.

Then:

1.  Log in to your **LifeTech OCMS Backend**.
2.  Go to **Packages**.
3.  Select **Plugins**.
4.  Install the downloaded **PlgMySweetalert** package.
5.  Enable or publish the plugin where required.

### Option 2 --- Install Directly from the Backend

From your LifeTech OCMS Backend:

1.  Go to **Packages → Plugins**.
2.  Select **Browse Online**.
3.  Search for **SweetAlert**.
4.  Select the plugin.
5.  LifeTech OCMS will automatically download and install the plugin.

## Loading SweetAlert in LifeTech OCMS

Before using SweetAlert, load the JavaScript library using the LifeTech
OCMS `ltSiteUrl()` helper:

``` php
<script src="<?= ltSiteUrl() ?>/includes/backend_assets/sweetalert/sweetalert.js"></script>
```

This loads the SweetAlert JavaScript asset from the LifeTech OCMS
backend assets directory.

Once the script has been loaded, you can use `Swal.fire()` in your
theme, module, plugin, component, layout, or view.

## Usage

### Basic Alert

``` javascript
Swal.fire('Hello from LifeTech OCMS!');
```

### Success Message

``` javascript
Swal.fire({
    title: 'Success!',
    text: 'Your changes have been saved successfully.',
    icon: 'success'
});
```

### Error Message

``` javascript
Swal.fire({
    title: 'Error!',
    text: 'Something went wrong.',
    icon: 'error'
});
```

### Warning Message

``` javascript
Swal.fire({
    title: 'Warning',
    text: 'Please check the information before continuing.',
    icon: 'warning'
});
```

## Complete LifeTech OCMS Example

The following example loads SweetAlert and displays a message when a
button is clicked:

``` php
<script src="<?= ltSiteUrl() ?>/includes/backend_assets/sweetalert/sweetalert.js"></script>

<button type="button" onclick="showMessage()">
    Show Message
</button>

<script>
    function showMessage() {
        Swal.fire({
            title: 'Welcome!',
            text: 'SweetAlert is working with LifeTech OCMS.',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    }
</script>
```

## Confirmation Dialog

SweetAlert is useful for confirming important actions before they are
executed.

``` javascript
Swal.fire({
    title: 'Are you sure?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, continue',
    cancelButtonText: 'Cancel'
}).then((result) => {
    if (result.isConfirmed) {
        // Execute the LifeTech OCMS action.
    }
});
```

Confirmation dialogs can be used before:

-   Deleting content
-   Removing records
-   Uninstalling packages
-   Logging out
-   Publishing or unpublishing content
-   Submitting important forms
-   Performing administrative actions

## Using with LifeTech OCMS

PlgMySweetalert can be used within:

-   Themes
-   Modules
-   Plugins
-   Components
-   Views
-   Layouts
-   Backend interfaces
-   Frontend interfaces

Example:

``` javascript
Swal.fire({
    title: 'Completed',
    text: 'The operation was completed successfully.',
    icon: 'success',
    confirmButtonText: 'OK'
});
```

## AJAX / API Example

SweetAlert can also display feedback after a LifeTech OCMS API or AJAX
operation.

``` javascript
fetch('/your-api-endpoint')
    .then(response => response.json())
    .then(data => {
        Swal.fire({
            title: 'Success',
            text: 'The request was completed successfully.',
            icon: 'success'
        });
    })
    .catch(error => {
        Swal.fire({
            title: 'Request Failed',
            text: 'Unable to complete the request.',
            icon: 'error'
        });
    });
```

Replace the example endpoint and response handling with the appropriate
LifeTech OCMS API implementation.

## Plugin Information

``` text
Package Name: PlgMySweetalert
Package Type: Plugin
Plugin Version: 2.0
Framework: LifeTech OCMS
Language/Technology: JavaScript / SweetAlert2
```

## Repository

https://github.com/ajayi-abolore/PlgMySweetalert

Clone the repository:

``` bash
git clone https://github.com/ajayi-abolore/PlgMySweetalert.git
```
 

## LifeTech OCMS Documentation

https://www.lifetech.host/hubs/Docs

## Compatibility

This repository currently contains **PlgMySweetalert v2.0**.

Always check the plugin package information and LifeTech OCMS version
requirements before installation.

## Author

**Ajayi Abolore A.**

LifeTech OCMS

https://www.lifetech.host

## License

This project is released under the **MIT License**.

See the `LICENSE` file included in this repository for details to read.
