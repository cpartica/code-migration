<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$baseDir = realpath(__DIR__ . '/../../../');
$testsBaseDir = $baseDir . '/tests/static';
require $baseDir . '/vendor/squizlabs/php_codesniffer/autoload.php';
$vendorAutoload = $baseDir . "/vendor/autoload.php";

/* 'composer install' validation */
if (file_exists($vendorAutoload)) {
    $composerAutoloader = include $vendorAutoload;
} else {
    throw new \Exception(
        'Vendor autoload is not found. Please run \'composer install\' under application root directory.'
    );
}

use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\Autoload\ClassLoaderWrapper;

AutoloaderRegistry::registerAutoloader(new ClassLoaderWrapper($composerAutoloader));

$autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
$autoloadWrapper->addPsr4('Magento\\', $testsBaseDir . '/testsuite/Magento/');
$autoloadWrapper->addPsr4(
    'Magento\\TestFramework\\',
    [
        $testsBaseDir . '/framework/Magento/TestFramework/',
        $testsBaseDir . '/../integration/framework/Magento/TestFramework/',
    ]
);
$autoloadWrapper->addPsr4('Magento\\', $baseDir . '/var/generation/Magento/');
