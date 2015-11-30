<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAliasMapping extends Command
{
    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Migration\Logger\Logger $logger
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     */
    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        $name = null
    ) {
        $this->logger = $logger;
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('generateAliasMapping')
            ->setDescription('Generate alias mapping from M1')
            ->addArgument(
                'm1',
                InputArgument::REQUIRED,
                'Base directory of M1'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $m1BaseDir = $input->getArgument('m1');

        if (!is_dir($m1BaseDir)) {
            $this->logger->error('m1 path doesn\'t exist or not a directory');
            return 255;
        }

        $configFiles = glob($m1BaseDir . '/app/code/core/Mage/*/etc/config.xml');

        $aliases = [
            'helper' => [],
            'block' => [],
            'model' => [],
        ];
        $types = array_keys($aliases);
        foreach ($configFiles as $configFile) {
            $configFileContent = file_get_contents($configFile);
            $config = new \Magento\Migration\Utility\M1\Config($configFileContent, $this->logger);
            foreach ($types as $type) {
                $aliasesForType = $config->getAliases($type . 's');
                $aliases[$type] = array_merge($aliases[$type], $aliasesForType);

            }
        }

        //add default
        $coreModules = glob($m1BaseDir . '/app/code/core/Mage/*');
        foreach ($coreModules as $coreModulePath) {
            $defaultAlias = lcfirst(basename($coreModulePath));
            foreach ($types as $type) {
                if (!isset($aliases[$type][$defaultAlias])) {
                    $alias = 'mage ' . $defaultAlias . ' ' . $type;
                    $alias = str_replace(' ', '_', ucwords($alias));
                    $aliases[$type][$defaultAlias] = $alias;
                }
            }
        }
        $outputFileName = BP . '/mapping/aliases.json';
        if (file_put_contents($outputFileName, json_encode($aliases, JSON_PRETTY_PRINT))) {
            $this->logger->info($outputFileName . ' was generated');
        } else {
            $this->logger->error('Could not write '.$outputFileName . '. check writing permissions');
        }
        return 0;
    }
}
