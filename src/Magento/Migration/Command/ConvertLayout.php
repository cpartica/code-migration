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
        \Magento\Migration\Mapping\AliasFactory $aliasFactory,
        \Magento\Migration\Mapping\Context $context,
        $name = null
    ) {
        $this->layoutConverterFactory = $layoutConverterFactory;
        $this->fileFactory = $fileFactory;
        $this->logger = $logger;
        $this->aliasFactory = $aliasFactory;
        $this->context = $context;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('convertLayout')
            ->setDescription('converts M1 layout handlers to M2 format')
            ->addArgument(
                'inputPath',
                InputArgument::REQUIRED,
                'Input directory of M2 module(s) already migrated with the structure migration tool'
            )
            ->addArgument(
                'outputPath',
                InputArgument::REQUIRED,
                'Output directory for the M2 converted layout files (can be the same value as the first parameter)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $m1StructureConverted = $input->getArgument('inputPath');
        $moduleOutputDirectory = $input->getArgument('outputPath');

        $this->context->setm1StructureConvertedDir($m1StructureConverted);
        $alias = $this->aliasFactory->create(['context' => $this->context]);
        $this->createAliasXMLFromJsonMapping($alias);

        //TODO: check if the output folder is already migrated

        if ($m1StructureConverted) {
            if (!is_dir($m1StructureConverted)) {
                $this->logger->error('inputPath path doesn\'t exist or not a directory');
                exit;
            }
        }

        if (!is_dir($moduleOutputDirectory)) {
            $this->logger->error('outputPath path doesn\'t exist');
            exit;
        }

        $layoutMigration = $this->layoutConverterFactory->create(
            ['inputPath' => $m1StructureConverted, 'outputPath' => $moduleOutputDirectory]
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

        //$this->deleteTemporaryXMLFromJsonMapping();
        $this->logger->info('Ending layout xml converter', []);

    }

    protected function getLayoutFiles($path)
    {
        $m1FileUtil = $this->fileFactory->create(['basePath' => $path]);
        if (file_exists($path . '/app/code')) {
            $files = $m1FileUtil->getFiles([$path . '/app/code'], '*/*/view/*/layout/*.xml', true);
        } else {
            $files = $m1FileUtil->getFiles([$path], '*/*/view/*/layout/*.xml', true);
        }
        $layoutFiles = [];
        foreach ($files as $file) {
            if (!preg_match('/Magento\/[^\/]+\/view\/[^\/]+/is', $file)) {
                $layoutFiles[] = $file;
            }
        }
        return $layoutFiles;
    }

    /**
     * needed for the xslt external loading of xml vars
     */
    protected function createAliasXMLFromJsonMapping($alias)
    {
        $file = __DIR__ . '/../../../../mapping/aliases.json';
        $xml = $this->jsonToXML($alias->getAllMapping());
        $xmlFile = preg_replace('"\.json"', '.xml', $file);
        file_put_contents($xmlFile, $xml);
    }

    /**
     * needed for the xslt external loading of xml vars
     */
    protected function deleteTemporaryXMLFromJsonMapping()
    {
        foreach (glob(__DIR__ . '/../../../../mapping/*.json') as $file) {
            $xmlFile = preg_replace('"\.json"', '.xml', $file);
            unlink($xmlFile);
        }
    }


    protected function jsonToXML($data)
    {
        // An array of serializer options
        $serializer_options = [
            'addDecl' => true,
            'encoding' => 'UTF-8',
            'indent' => '  ',
            'rootName' => 'json',
            'mode' => 'simplexml'
        ];

        $serializer = new \XML_Serializer($serializer_options);
        if ($serializer->serialize($data)) {

            return $serializer->getSerializedData();
        } else {
            return false;
        }
    }
}
