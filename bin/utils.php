<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require'env.php';

error_reporting(E_ALL);

try {
    require BP . '/vendor/autoload.php';
} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    exit(1);
}


use Magento\Framework\App\Bootstrap;
use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\Autoload\ClassLoaderWrapper;

$vendorDir = require BP . '/app/etc/vendor_path.php';
$vendorAutoload = BP . "/{$vendorDir}/autoload.php";

/* 'composer install' validation */
if (file_exists($vendorAutoload)) {
    $composerAutoloader = include $vendorAutoload;
} else {
    throw new \Exception(
        'Vendor autoload is not found. Please run \'composer install\' under application root directory.'
    );
}

AutoloaderRegistry::registerAutoloader(new ClassLoaderWrapper($composerAutoloader));

// Sets default autoload mappings, may be overridden in Bootstrap::create
\Magento\Framework\App\Bootstrap::populateAutoloader(BP, []);
$params = [];
$bootstrap = Bootstrap::create(BP, $params);

use Symfony\Component\Console\Application;
use Magento\Migration\Internal\Command\Aggregator;

/** @var Application $application */
$application = $bootstrap->getObjectManager()->create('Symfony\Component\Console\Application');
/** @var Aggregator $aggregator */
$aggregator = $bootstrap->getObjectManager()->create('Magento\Migration\Internal\Command\Aggregator');
$aggregator->initialize();
$aggregator->populateCommands($application);

$application->run();
