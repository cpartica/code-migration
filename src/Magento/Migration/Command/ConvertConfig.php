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

class ConvertConfig extends Command
{
    /**
     * @var \Magento\Migration\Code\ConfigConverter
     */
    protected $configConverter;

    /**
     * @var \Magento\Migration\Utility\M1\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Mapping\AliasFactory
     */
    protected $aliasFactory;

    /**
     * @var \Magento\Migration\Mapping\ClassMappingFactory
     */
    protected $classMappingFactory;

    /**
     * @var \Magento\Migration\Mapping\Context
     */
    protected $context;


    /**
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Mapping\Context $context
     * @param \Magento\Migration\Code\ConfigConverter $configConverter
     * @param \Magento\Migration\Utility\M1\FileFactory $fileFactory
     * @param \Magento\Migration\Mapping\AliasFactory $aliasFactory
     * @param \Magento\Migration\Mapping\ClassMappingFactory $classMappingFactory
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     */
    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Mapping\Context $context,
        \Magento\Migration\Code\ConfigConverter $configConverter,
        \Magento\Migration\Utility\M1\FileFactory $fileFactory,
        \Magento\Migration\Mapping\AliasFactory $aliasFactory,
        \Magento\Migration\Mapping\ClassMappingFactory $classMappingFactory,
        $name = null
    ) {
        $this->configConverter = $configConverter;
        $this->fileFactory = $fileFactory;
        $this->logger = $logger;
        $this->aliasFactory = $aliasFactory;
        $this->classMappingFactory = $classMappingFactory;
        $this->context = $context;
        parent::__construct($name);
    }

    /**
     * configure the command
     * @return void
     */
    protected function configure()
    {
        $this->setName('convertConfig')
            ->setDescription('converts M1 etc config to M2')
            ->addArgument(
                'inputPath',
                InputArgument::REQUIRED,
                'Input directory of M2 module(s) already migrated with the structure migration tool'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $m1StructureConverted = $input->getArgument('inputPath');

        //TODO: check if the output folder is already migrated

        $this->context->setm1StructureConvertedDir($m1StructureConverted);

        $alias = $this->aliasFactory->create(['context' => $this->context]);
        $classMapping = $this->classMappingFactory->create();
        
        $this->createAliasXMLFromJsonMapping($alias);
        $this->createClassMappingXMLFromJson($classMapping);

        if ($m1StructureConverted) {
            if (!is_dir($m1StructureConverted)) {
                $this->logger->error('inputPath path doesn\'t exist or not a directory');
                exit;
            }
        }

        $this->logger->info('Starting etc config xml converter for '.$m1StructureConverted, []);

        $files = $this->getConfigFiles($m1StructureConverted);
        foreach ($files as $file) {
            $this->configConverter->processConfig($file);
        }
        $this->deleteTemporaryXMLFromJsonMapping();
        $this->logger->info('Ending etc config xml converter', []);
    }

    /**
     * @param string $path
     * @return string[]
     */
    protected function getConfigFiles($path)
    {
        //input expects the same structure as module structure conversion wrote the files in
        //eg: app/code/*vendor*/*module*
        $m1FileUtil = $this->fileFactory->create(['basePath' => $path]);
        if (file_exists($path . '/app/code')) {
            $files = $m1FileUtil->getFiles([$path . '/app/code/*/*/etc'], '*.xml', true);
        } else {
            $files = $m1FileUtil->getFiles([$path] . '*/*/etc', '*.xml', true);
        }
        $configFiles = [];
        foreach ($files as $file) {
            if (!preg_match('/Magento\/[^\/]+\/etc\/[^\/]+/is', $file)) {
                $configFiles[] = $file;
            }
        }
        return $configFiles;
    }

    /**
     * needed for the xslt external loading of xml vars
     * @param \Magento\Migration\Mapping\Alias $alias
     * @return void
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
     * @param \Magento\Migration\Mapping\ClassMapping $classMapping
     * @return void
     */
    protected function createClassMappingXMLFromJson($classMapping)
    {
        $file = __DIR__ . '/../../../../mapping/class_mapping_manual.json';
        $xml = $this->jsonToXML($classMapping->getAllClassMapping());
        $xmlFile = preg_replace('"\.json"', '.xml', $file);
        file_put_contents($xmlFile, $xml);
    }

    /**
     * needed for the xslt external loading of xml vars
     * @return void
     */
    protected function deleteTemporaryXMLFromJsonMapping()
    {
        foreach (glob(__DIR__ . '/../../../../mapping/*.json') as $file) {
            $xmlFile = preg_replace('"\.json"', '.xml', $file);
            unlink($xmlFile);
        }
    }

    /**
     * @param array $data
     * @return bool|string
     */
    protected function jsonToXML($data)
    {
        // An array of serializer options
        $serializerOptions = [
            'addDecl' => true,
            'encoding' => 'UTF-8',
            'indent' => '  ',
            'rootName' => 'json',
            'mode' => 'simplexml'
        ];

        $serializer = new \XML_Serializer($serializerOptions);
        if ($serializer->serialize($data)) {
            return $serializer->getSerializedData();
        } else {
            return false;
        }
    }
}
