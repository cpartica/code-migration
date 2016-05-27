<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

error_reporting(E_ERROR);

try {
    $composerAutoloader = require BP . '/vendor/autoload.php';
} catch (\Exception $e) {
    echo "Error! There was a problem with the composer autoloader." . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}

function exception_error_handler($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity == E_ALL)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");

use Magento\Framework\App\Bootstrap;
use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\Autoload\ClassLoaderWrapper;

AutoloaderRegistry::registerAutoloader(new ClassLoaderWrapper($composerAutoloader));

// Sets default autoload mappings, may be overridden in Bootstrap::create
\Magento\Framework\App\Bootstrap::populateAutoloader(BP, []);
$params = [];
$bootstrap = Bootstrap::create(BP, $params);
