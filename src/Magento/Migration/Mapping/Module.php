<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Mapping;

class Module
{
    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Migration\Logger\Logger $logger
     */
    public function __construct(\Magento\Migration\Logger\Logger $logger)
    {
        $this->logger = $logger;
        $mappingFile = BP . '/mapping/module_mapping.json';
        if (file_exists($mappingFile)) {
            $content = file_get_contents($mappingFile);

            $this->mapping = json_decode($content, true);
        } else {
            $this->logger->warn("Can not locate module mapping file: " . $mappingFile);
            $this->mapping = [];
        }
    }

    /**
     * @param string $moduleName
     * @return string|null
     */
    public function mapModule($moduleName)
    {
        if (isset($this->mapping[$moduleName]) && isset($this->mapping[$moduleName]['m2module'])) {
            if (is_array($this->mapping[$moduleName]['m2module'])) {
                return end($this->mapping[$moduleName]['m2module']);
            } else {
                return $this->mapping[$moduleName]['m2module'];
            }
        } else {
            $this->logger->debug("Failed to map module: " . $moduleName);
            return null;
        }
    }
}
