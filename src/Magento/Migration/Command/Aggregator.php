<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\ObjectManagerInterface;

class Aggregator
{
    /**
     * @var string
     */
    protected $commandDir = __DIR__;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Migration\Logger\Manager
     */
    protected $loggerManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Migration\Logger\Manager $loggerManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Migration\Logger\Manager $loggerManager
    ) {
        $this->objectManager = $objectManager;
        $this->loggerManager = $loggerManager;
    }

    /**
     * @return void
     * @throws \Magento\Migration\Utility\Exception
     */
    public function initialize()
    {
        $this->loggerManager->process('info');
    }
    /**
     * @param Application $app
     * @return void
     */
    public function populateCommands(Application $app)
    {
        $currentDir = strlen(BP . '/src');
        //TODO: search recursively
        $files = glob($this->commandDir . '/*.php');
        foreach ($files as $file) {
            $className = substr($file, $currentDir, -4);
            $className = str_replace('/', '\\', $className);

            if (is_subclass_of($className, '\\Symfony\\Component\\Console\\Command\\Command')) {
                $command = $this->objectManager->create($className);
                $app->add($command);
            }
        }
        return;
    }
}
