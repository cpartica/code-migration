<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Mapping;

class TableName
{
    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var null|string
     */
    protected $m1BaseDir = null;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Mapping\Context
     */
    protected $context;

    /**
     * @var \Magento\Migration\Utility\File
     */
    protected $file;

    /**
     * @var \Magento\Migration\Utility\M1\ConfigFactory
     */
    protected $configFactory;

    /**
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Mapping\Context $context
     * @param \Magento\Migration\Utility\File $file
     * @param \Magento\Migration\Utility\M1\ConfigFactory $configFactory
     */
    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Mapping\Context $context,
        \Magento\Migration\Utility\File $file,
        \Magento\Migration\Utility\M1\ConfigFactory $configFactory
    ) {
        $this->logger = $logger;
        $this->context = $context;
        $this->file = $file;
        $this->configFactory = $configFactory;
        $this->getAllMapping();
    }

    /**
     * @param string $tableName
     * @param string $entity
     * @return string|null
     */
    public function mapTableName($tableName, $entity)
    {
        if (isset($this->mapping[$tableName]) && isset($this->mapping[$tableName][$entity])) {
            if (is_array($this->mapping[$tableName][$entity])) {
                return end($this->mapping[$tableName][$entity]);
            } else {
                return $this->mapping[$tableName][$entity];
            }
        } else {
            $this->logger->warn("Could not map table name " . $tableName . ' of type ' . $entity);
            return null;
        }
    }

    /**
     * Populate alias map from json
     *
     * @return array
     */
    public function processMappingFromJson()
    {
        $mappingFile = BP . '/mapping/table_names_mapping.json';
        if (file_exists($mappingFile)) {
            $content = file_get_contents($mappingFile);
            $this->mapping = json_decode($content, true);
        } else {
            $this->mapping = [];
        }
    }

    /**
     * Populate and merge alias map from structure
     *
     * @return array
     */
    public function processMappingFromPath()
    {
        if (empty($this->mapping)) {
            $this->mapping = [];
        }
        $basePath = $this->context->getm1StructureConvertedDir();
        if (is_file($this->context->getm1StructureConvertedDir())) {
            $basePath = dirname($this->context->getm1StructureConvertedDir());
        }

        $searchArray = [
            $basePath,
            $basePath . "/*",
            $basePath . "/*/*",
            $basePath . "/*/*/*",
            $basePath . "/*/*/*/*",
            $basePath . "/*/*/*/*/*"
        ];
        if (preg_match('/(.+app\/code)/', $basePath, $match)) {
            $searchArray[] = $match[0];
        }
        foreach ($this->file->getFiles(
            $searchArray,
            "etc/config.xml"
        ) as $configFiles) {
            /** @var \Magento\Migration\Utility\M1\Config $config */
            $config = $this->configFactory->create(['configFileContent' => file_get_contents($configFiles)]);
            $mapping = $config->getTableAliases();
            if (count($mapping) > 0) {
                $this->mapping = array_merge($this->mapping, $mapping);
            }
        }
        return  $this->mapping;
    }

    /**
     * Populate alias map
     *
     * @return array
     */
    public function getAllMapping()
    {
        if (empty($this->mapping)) {
            $this->processMappingFromJson();
        }
        if ($this->context->getm1StructureConvertedDir()) {
            $this->processMappingFromPath();
        }
        return $this->mapping;
    }
}
