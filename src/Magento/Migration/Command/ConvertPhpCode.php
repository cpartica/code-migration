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

class ConvertPhpCode extends Command
{
    const CONVERTED_FILE_EXT = '.converted';

    /**
     * @var \Magento\Migration\Utility\File
     */
    protected $fileUtil;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\Converter
     */
    protected $converter;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Migration\Code\Converter $converter
     * @param \Magento\Migration\Utility\File $fileUtil
     * @param \Magento\Migration\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Migration\Code\Converter $converter,
        \Magento\Migration\Utility\File $fileUtil,
        \Magento\Migration\Logger\Logger $logger
    ) {
        parent::__construct();
        $this->objectManager = $objectManager;
        $this->converter = $converter;
        $this->fileUtil = $fileUtil;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('convertPhpCode')
            ->setDescription('Convert php code from M1 to M2')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to file or directory'
            )->addArgument(
                'm1BaseDir',
                InputArgument::OPTIONAL,
                'Magento 1 base directory that include the module that is to be converted.'
            )->addArgument(
                'm2BaseDir',
                InputArgument::OPTIONAL,
                'Magento 2 base directory that include the module that is to be converted.'
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
        $path = $input->getArgument('path');
        $m1BaseDir = $input->getArgument('m1BaseDir');
        $m2BaseDir = $input->getArgument('m2BaseDir');

        $this->objectManager->get('\Magento\Migration\Mapping\Context')
            ->setM1BaseDir($m1BaseDir)
            ->setM2BaseDir($m2BaseDir)
            ->setm1StructureConvertedDir($path);

        if ($m2BaseDir) {
            \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader()
                ->addPsr4('Magento\\', $m2BaseDir . '/app/code/Magento/');
            \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader()
                ->addPsr4('Magento\\Framework\\', $m2BaseDir . '/lib/internal/Magento/Framework', true);
        }

        if (is_dir($path)) {
            $phpFiles = $this->fileUtil->getFiles([$path], '*.php', true);
        } elseif (is_file($path)) {
            $phpFiles = [$path];
        } else {
            $this->logger->error('Invalid path: ' . $path);
            return null;
        }

        foreach ($phpFiles as $filePath) {
            $this->logger->info('Processing file ' . $filePath);
            try {
                $fileContent = file_get_contents($filePath);
                $additionalFiles = [];
                $this->converter->setFilePath($filePath);
                $fileContent = $this->converter->split($fileContent, $additionalFiles);
                $convertedContent = $this->converter->convert($fileContent);
                $outputFilePath = $this->converter->getFilePath() . self::CONVERTED_FILE_EXT;
                file_put_contents($outputFilePath, $convertedContent);
                foreach ($additionalFiles as $additionalFilePath) {
                    $fileContent = file_get_contents($additionalFilePath);
                    $convertedContent = $this->converter->setFilePath($additionalFilePath)->convert($fileContent);
                    $outputFilePath = $this->converter->getFilePath();
                    file_put_contents($outputFilePath, $convertedContent);
                }
            } catch (\Exception $e) {
                $this->logger->error("caught exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            }
            $this->logger->info('Finished processing file ' . $filePath);
        }
        return 0;
    }
}
