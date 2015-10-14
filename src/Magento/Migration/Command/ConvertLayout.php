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

class ConvertLayout extends Command
{
    /**
     * @var \Magento\Migration\Code\LayoutConverterFactory
     */
    protected $layoutConverterFactory;

    /**
     * @var \Magento\Migration\Utility\M1\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;


    /**
     * @param \Magento\Migration\Code\LayoutConverterFactory $layoutConverterFactory
     * @param \Magento\Migration\Utility\M1\FileFactory $fileFactory
     * @param \Magento\Migration\Logger\Logger $logger
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     */
    public function __construct(
        \Magento\Migration\Code\LayoutConverterFactory $layoutConverterFactory,
        \Magento\Migration\Utility\M1\FileFactory $fileFactory,
        \Magento\Migration\Logger\Logger $logger,
        $name = null
    ) {
        $this->layoutConverterFactory = $layoutConverterFactory;
        $this->fileFactory = $fileFactory;
        $this->logger = $logger;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('convertLayout')
            ->setDescription('converts M1 layout handlers to M2 format')
            ->addArgument(
                'm2',
                InputArgument::REQUIRED,
                'Output directory of M2 module(s) already migrated with the structure migration tool'
            )
            ->addArgument(
                'm1',
                InputArgument::OPTIONAL,
                'Input directory of M1 installation'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $moduleBaseDirectory = $input->getArgument('m1');
        $moduleOutputDirectory = $input->getArgument('m2');

        //TODO: check if the m1 folder
        //TODO: check if the output folder is already migrated

        if ($moduleBaseDirectory) {
            if (!is_dir($moduleBaseDirectory)) {
                $this->logger->error('m1 path doesn\'t exist or not a directory');
                exit;
            }
        }

        if (!is_dir($moduleOutputDirectory)) {
            $this->logger->error('m2 path doesn\'t exist');
            exit;
        }

        $layoutMigration = $this->layoutConverterFactory->create(
            ['m2Path' => $moduleOutputDirectory, 'm1Path' => $moduleOutputDirectory]
        );

        $this->logger->info('Starting layout xml converter', []);

        $files = $this->getLayoutFiles($moduleOutputDirectory);
        $cnt=0;
        foreach ($files as $file) {
           $layoutMigration->processLayoutHandlers($file);
            $cnt++;
        }
        if ($cnt == 0) {
            $this->logger->warn($cnt . ' layouts were converted', []);
        }

        $this->logger->info('Ending layout xml converter', []);

    }

    protected function getLayoutFiles($m2Path)
    {
        $m1FileUtil = $this->fileFactory->create(['basePath' => $m2Path]);
        $files = $m1FileUtil->getFiles([$m2Path . '/app/code'], '*/*/view/*/layout/*.xml', true);
        $configs = [];
        foreach ($files as $file) {
            if (!preg_match('/Magento\/[^\/]+\/view\/[^\/]+/is', $file)) {
                $configs[] = $file;
            }
        }
        return $configs;
    }
}
