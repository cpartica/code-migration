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

class MigrateModuleStructure extends Command
{
    /**
     * @var \Magento\Migration\Code\ModuleMigrationFactory
     */

    protected $moduleMigrationFactory;

    /**
     * @var \Magento\Migration\Utility\M1\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;


    /**
     * @param \Magento\Migration\Code\ModuleMigrationFactory $moduleMigrationFactory
     * @param \Magento\Migration\Utility\M1\FileFactory $fileFactory
     * @param \Magento\Migration\Logger\Logger $logger
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     */
    public function __construct(
        \Magento\Migration\Code\ModuleMigrationFactory $moduleMigrationFactory,
        \Magento\Migration\Utility\M1\FileFactory $fileFactory,
        \Magento\Migration\Logger\Logger $logger,
        $name = null
    ) {
        $this->moduleMigrationFactory = $moduleMigrationFactory;
        $this->fileFactory = $fileFactory;
        $this->logger = $logger;
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('migrateModuleStructure')
            ->setDescription('migrates M1 moudle(s) to M2 structure')
            ->addArgument(
                'm1',
                InputArgument::REQUIRED,
                'Input directory of M1 module(s) or M1 installation'
            )            ->addArgument(
                'm2',
                InputArgument::REQUIRED,
                'Output directory of M2 module(s) or M2 installation'
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
        $moduleBaseDirectory = $input->getArgument('m1');
        $moduleOutputDirectory = $input->getArgument('m2');

        // Normalize path to front slashes which will work on Windows as well
        if (DIRECTORY_SEPARATOR == "\\") {
            $moduleBaseDirectory   = str_replace("\\", "/", $moduleBaseDirectory);
            $moduleOutputDirectory = str_replace("\\", "/", $moduleOutputDirectory);
        }

        if (!is_dir($moduleBaseDirectory)) {
            $this->logger->error('m1 path doesn\'t exist or not a directory');
            return 255;
        }

        if (!is_dir($moduleOutputDirectory)) {
            $this->logger->error('m2 path doesn\'t exist');
            return 255;
        }

        $m1FileUtil = $this->fileFactory->create(['basePath' => $moduleBaseDirectory]);

        $moduleMigration = $this->moduleMigrationFactory->create(
            [
                'm1Path' => $moduleBaseDirectory,
                'm2Path' => $moduleOutputDirectory,
            ]
        );

        $this->logger->info('Starting module structure converter', []);

        $files = $m1FileUtil->getModulesConfigFiles();
        $cnt=0;
        foreach ($files as $location => $value) {
            $moduleMigration->moveModuleFiles($value, $location);
            $cnt++;
        }
        if ($cnt == 0) {
            $this->logger->warn($cnt . ' modules were converted', []);
        }

        $this->logger->info('Ending module structure converter', []);
        return 0;
    }
}
