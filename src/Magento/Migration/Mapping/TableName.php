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
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Migration\Utility\File
     */
    protected $file;

    /**
     * @param \Magento\Migration\Logger\Logger $logger
     * @param Context $context
     * @param \Magento\Migration\Utility\File $file
     */
    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        Context $context,
        \Magento\Migration\Utility\File $file
    ) {
        $this->logger = $logger;
        $this->context = $context;
        $this->file = $file;
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
            return $this->mapping[$tableName][$entity];
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

        foreach ($this->file->getFiles(
            [
                $basePath,
                $basePath . "/*",
                $basePath . "/*/*",
                $basePath . "/*/*/*",
                $basePath . "/*/*/*/*",
                $basePath . "/*/*/*/*/*",
                $basePath . "/..",
                $basePath . "/../..",
                $basePath . "/../../..",
                $basePath . "/../../../..",
                $basePath . "/../../../../..",
            ],
            "etc/config.xml"
        ) as $configFiles) {
            $content = file_get_contents($configFiles);
            $config = new \Magento\Migration\Utility\M1\Config($content);
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
