<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Internal\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTableNameMapping extends Command
{
    /**
     * @var array
     */
    protected $moduleMapping = [];

    /** @var \Magento\Framework\Simplexml\ConfigFactory */
    protected $configFactory;

    /** @var \Magento\Framework\Filesystem\Driver\File */
    protected $file;

    /** @var \Magento\Migration\Logger\Logger */
    protected $logger;

    /**
     * @param \Magento\Framework\Simplexml\ConfigFactory $configFactory
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Migration\Logger\Logger $logger
     * @throws \LogicException When the command name is empty
     */
    public function __construct(
        \Magento\Framework\Simplexml\ConfigFactory $configFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Migration\Logger\Logger $logger
    ) {
        $this->configFactory = $configFactory;
        $this->file = $file;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('generateTableNamesMapping')
            ->setDescription('Generate table names mapping from M1 to M2')
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

        $tableNamesMapping = [];

        $configFiles = $this->file->search('code/*/*/*/etc/config.xml', $m1BaseDir . '/app');
        foreach ($configFiles as $configFile) {
            $content = $this->file->fileGetContents($configFile);
            $config = new \Magento\Migration\Utility\M1\Config($content, $this->logger);
            $mapping = $this->mapTableNames($config);
            if (count($mapping) > 0) {
                $tableNamesMapping = array_merge($mapping, $tableNamesMapping);
            }
        }

        $outputFileName = BP . '/mapping/table_names_mapping.json';
        if (file_put_contents($outputFileName, json_encode($tableNamesMapping, JSON_PRETTY_PRINT))) {
            $this->logger->info($outputFileName . ' was generated');
        } else {
            $this->logger->error('Could not write ' . $outputFileName . '. check writing permissions');
            return 255;
        }
        return 0;
    }

    /**
     * @param \Magento\Migration\Utility\M1\Config $config
     * @return array
     */
    protected function mapTableNames($config)
    {
        return $config->getTableAliases();
    }
}
