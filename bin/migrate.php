<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'env.php';
require BP . '/app/bootstrap.php';

use Symfony\Component\Console\Application;
use Magento\Migration\Command\Aggregator;

/** @var Application $application */
$application = $bootstrap->getObjectManager()->create('Symfony\Component\Console\Application');
/** @var Aggregator $aggregator */
$aggregator = $bootstrap->getObjectManager()->create('Magento\Migration\Command\Aggregator');
$aggregator->initialize();
$aggregator->populateCommands($application);
$application->run();
