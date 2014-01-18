<?php

/*
 * This file is part of the RollerworksNavigationBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Composer\Autoload\ClassLoader;

error_reporting(E_ALL | E_STRICT);

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    throw new \RuntimeException('Did not find vendor/autoload.php. Did you run "composer install --dev"?');
}

if (version_compare(PHP_VERSION, '5.4', '>=') && gc_enabled()) {
    // Disabling Zend Garbage Collection to prevent segfaults with PHP5.4+
    // https://bugs.php.net/bug.php?id=53976
    gc_disable();
}

/**
 * @var $loader ClassLoader
 */
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Rollerworks\\Bundle\\NavigationBundle\\Tests', __DIR__);

return $loader;
