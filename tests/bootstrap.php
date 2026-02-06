<?php

declare(strict_types=1);

/*
 * Bootstrap file for PHPUnit tests.
 *
 * This file is loaded before any tests run, ensuring that the composer
 * attribute collector's attributes file is loaded early enough for
 * method and property discovery to work correctly.
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load the attributes file for composer-attribute-collector
// This must be loaded before any tests run to ensure method/property
// attributes are available for discovery
$attributesFile = __DIR__ . '/../vendor/attributes.php';
if (file_exists($attributesFile)) {
    require_once $attributesFile;
}
