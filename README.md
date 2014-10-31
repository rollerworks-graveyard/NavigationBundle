RollerworksNavigationBundle
===========================

The RollerworksNavigationBundle adds the ability to define menu-structures
and breadcrumbs for the KnpMenuBundle in the app-config.

## Installation

> Please make sure you install the KnpMenuBundle before continuing.

### Step 1: Using Composer (recommended)

To install the RollerworksNavigationBundle with Composer just add the following to your
`composer.json` file:

```js
// composer.json
{
    // ...
    require: {
        // ...
        "rollerworks/navigation-bundle": "1.*"
    }
}
```

Then, you can install the new dependencies by running Composer's ``update``
command from the directory where your ``composer.json`` file is located:

```bash
$ php composer.phar update rollerworks/navigation-bundle
```

Now, Composer will automatically download all required files, and install them
for you. All that is left to do is to update your ``AppKernel.php`` file, and
register the new bundle:

```php
<?php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Rollerworks\Bundle\NavigationBundle\RollerworksNavigationBundle(),
    // ...
);
```

## Documentation

> Currently only YAML and the PHP format are supported.
> XML support is planned for later.

### Expressions

The "parameters" config supports the Symfony ExpressionLanguage.

Values starting with an '@'-sign will be treated as expressions, to escape the value use a double '@'-sign
like '@@your value', the leading '@@' is then converted to '@'.

> **Note:** Expressions require at least version 2.4 of the DependencyInjection component.

### Defining menus

Menus can be declared under the `rollerworks_navigation.menus` configuration tree,
you can define as many menus as you need.

Each menu is registered in the Service Container as `rollerworks_navigation.menu.[menu-name]`
and is tagged for the KnpMenu loader by the menu-name.

```yaml
rollerworks_navigation:
    menus:
        menu-name:
            template: ~ # optional template, used by the Menu builder
            items:
                -
                    label:             ~                            # Label of the breadcrumb will be translated with the translator_domain
                    translator_domain: Menus                        # translator domain for the label
                    route:             { name: ~, parameters: { } } # name can not be empty
                    items:             []                           # Sub-level items, same as this example (unlimited depth nesting)

                    # alternatively you can reference a service for getting the Menu object
                    # The service must return a Knp\Menu\ItemInterface instance
                    service:
                        id:         ~  # service-id, can not be empty
                        method:     ~  # method to call on the service, can not be empty
                        parameters: [] # Parameter to pass to the method (same as service container parameters, including Expression)
```

**Note:** You can only either use the static, service or expression.

When using a service or expression sub-items must provided by the returned MenuItem object.

### Defining breadcrumbs

Breadcrumbs can be declared under the `rollerworks_navigation.breadcrumbs` configuration tree,
you define as many breadcrumbs as you need.

In comparison with menus, deeper breadcrumbs reference there parent by name,
the parent may in turn reference another parent.

> **Note.** Any Circular reference is will throw an exception.

Its a good practice to keep the related breadcrumb(s) in the bundle itself,
and use a 'root-bundle' to reference from.

Use the importing capabilities of the Symfony Config component for
importing config files from the bundles.

> The final structure is normalized before registering, so no complex building or resolving
> is done that runtime.

Each breadcrumb is registered in the Service Container as `rollerworks_navigation.breadcrumbs.[breadcrumb-name]`
and is tagged for the KnpMenu loader by the menu-name.

```yaml
rollerworks_navigation:
    breadcrumbs:
        breadcrumb-name:
            parent:            ~                            # Optional parent breadcrumb to reference (by name)

            # Static configuration
            label:             ~                            # Label of the breadcrumb will be translated with the translator_domain
            translator_domain: Breadcrumbs                  # translator domain for the label
            route:             { name: ~, parameters: { } } # name can not be empty

            # alternatively you can reference a service for getting the Menu object
            # The service must return a Knp\Menu\ItemInterface instance
            service:
                id:         ~  # service-id, can not be empty
                method:     ~  # method to call on the service, can not be empty
                parameters: [] # Parameter to pass to the method (same as service container parameters, including Expression)
```

## License

This bundle is released under the MIT license.
See the bundled LICENSE file for details.
