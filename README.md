RollerworksNavigationBundle
===========================

The RollerworksNavigationBundle adds the ability to define menu-structures
and breadcrumbs for the KnpMenuBundle in your application configuration.

Requirements
------------

You need at least PHP 5.3 and the KnpMenuBundle already installed and configured.

Installation
------------

**Note:** The RollerworksNavigationBundle is an addition to the KnpMenuBundle,
make sure you have the KnpMenuBundle installed and properly configured.

The recommended way to install the RollerworksNavigationBundle is through [Composer].

Require the `rollerworks/navigation-bundle` package by running:

```bash
$ php composer.phar require rollerworks/navigation-bundle
```

Now, Composer will automatically download all the required files, and install
them for you. After this enable the bundle in the kernel:

```php
<?php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Rollerworks\Bundle\NavigationBundle\RollerworksNavigationBundle(),
    // ...
);
```

[Composer]: https://getcomposer.org/

Usage
-----

### Expressions

Since Symfony 2.4 you can make some parts of your application configuration more dynamic
by using the [Symfony ExpressionLanguage]. As the RollerworksNavigationBundle registers navigation
definitions as Services in the DependencyInjection Container you can also use expressions
for the "parameters" config parameter.

In practice you can use expressions for navigation translation/route and service parameters.

Parameters values starting with an `@` will be treated as expressions, to mark the value
as "literal" use a double `@` like `'@@your value'`, which is converted to `'@your value'`.

**Note:** Only the *first* leading `@@` is converted a single `@`, other `@`'s will
be left unchanged. A value like 'my@value' is not transformed to an expression.

[Symfony ExpressionLanguage]: http://symfony.com/doc/current/components/expression_language/introduction.html

### Using a dedicated service for menu items/breadcrumbs

If your navigation is to dynamic you may also use a dedicated service.
The service must return a `Knp\Menu\ItemInterface` instance.

### Defining menus

Menus are defined under the `rollerworks_navigation.menus` configuration tree,
you can add as many menus as you need.

Each menu is registered in the Service Container as `rollerworks_navigation.menu.[menu-name]`
and is tagged for the KnpMenu loader by the 'menu-name'.

```yaml
rollerworks_navigation:
    menus:
        menu-name:
            template: ~ # optional template, used by the Menu builder
            items:
                item-name: # name of the item, eg. home, products, and such.
                    label:             ~                            # Label of the menu-item, this will be translated with the translator_domain
                    translator_domain: Menus                        # translator domain for the label
                    route:             { name: ~, parameters: { } } # The route.name can not be empty, parameters is optional
                                                                    # route can also be only a string (route name)
                    uri:               ~                            # Alternatively you can use a URI instead of a route
                    items:             []                           # Sub-level items, same as this example (unlimited depth nesting)

                    # If your menu item is to dynamic you may also use a dedicated service.
                    # The service must return a Knp\Menu\ItemInterface instance.
                    service:
                        id:         ~  # service-id, can not be empty
                        method:     ~  # optional method to call on the service
                        parameters: [] # Parameter to pass to the method (same as service container parameters, including Expression support)

                    # Need full control? Speficy an expression get a Knp\Menu\ItemInterface instance
                    # like: service('acme_customer.navigation').getMenu()
                    expression: ~
```

**Note:** You can only either use a static, service or expression per menu item.

When using a service or expression sub-items must provided by the returned MenuItem object.

### Defining breadcrumbs

Breadcrumbs are defined under the `rollerworks_navigation.breadcrumbs` configuration tree,
you define as many breadcrumbs as you need.

Other then menus, deeper breadcrumbs reference there parent by name,
the parent may in turn reference another parent.

**Tip:**

> It's a good practice to keep the related breadcrumb(s) in there own bundle,
> and use a 'root-bundle' to reference from.
>
> Use the importing capabilities of the Symfony Config component for
> importing config files from other bundles.

The final structure is normalized before registering, so no complex building or resolving
is done that runtime.

Each breadcrumb is registered in the Service Container as `rollerworks_navigation.breadcrumbs.[breadcrumb-name]`
and is tagged for the KnpMenu loader by the 'breadcrumb-name'.

**Caution:**

> Each breadcrumb name must be unique thought-out the application.
> It's advised to use the same conventions as used for service-id's.
>
> For example 'homepage' could be named 'acme_breadcrumbs.homepage'.

```yaml
rollerworks_navigation:
    breadcrumbs:
        breadcrumb-name: # name of the breadcrumb item. Must be unique though out the application.
            parent:            ~                            # Optional parent breadcrumb to reference (by name)

            # Static configuration
            label:             ~                            # Label of the breadcrumb, this will be translated with the translator_domain
            translator_domain: Breadcrumbs                  # translator domain for the label
            route:             { name: ~, parameters: { } } # The route.name can not be empty, parameters is optional
                                                            # route can also be only a string (route name)
            uri:               ~                            # Alternatively you can use a URI instead of a route

            # If your breadcrumb is to dynamic you may also use a dedicated service.
            # The service must return a Knp\Menu\ItemInterface instance.
            service:
                id:         ~  # service-id, can not be empty
                method:     ~  # optional method to call on the service
                parameters: [] # Parameter to pass to the method (same as service container parameters, including Expression support)

            # Need full control? Speficy an expression get a Knp\Menu\ItemInterface instance
            # like: service('acme_customer.navigation').getBreadcrumb()
            expression: ~

```

Versioning
----------

For transparency and insight into the release cycle, and for striving
to maintain backward compatibility, RollerworksSearch is maintained under
the Semantic Versioning guidelines as much as possible.

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

* Breaking backward compatibility bumps the major (and resets the minor and patch)
* New additions without breaking backward compatibility bumps the minor (and resets the patch)
* Bug fixes and misc changes bumps the patch

For more information on SemVer, please visit <http://semver.org/>.

License
-------

The source of this package is subject to the MIT license that is bundled
with this source code in the file [LICENSE](LICENSE).

Contributing
------------

This is an open source project. If you'd like to contribute,
please read the [Contributing Guidelines][1]. If you're submitting
a pull request, please follow the guidelines in the [Submitting a Patch][2] section.

[1]: https://github.com/rollerworks/contributing
[2]: https://contributing.readthedocs.org/en/latest/code/patches.html
[3]: http://docutils.sourceforge.net/rst.html
[4]: http://sphinx-doc.org/
[5]: https://contributing.readthedocs.org/en/latest/documentation/format.html
[6]: http://rollerworkssearch.readthedocs.org/en/latest/
