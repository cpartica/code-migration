<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Mapping;

class ClassMapping
{
    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    public function __construct(\Magento\Migration\Logger\Logger $logger)
    {
        $this->logger = $logger;

        $mappingFile = BP . '/mapping/class_mapping.json';
        $content = file_get_contents($mappingFile);
        if ($content) {
            $this->mapping = json_decode($content, true);
        } else {
            $this->logger->warn("Could not open class mapping file: " . $mappingFile);
        }

        $mappingFile = BP . '/mapping/class_mapping_manual.json';
        $content = file_get_contents($mappingFile);
        if ($content) {
            $mapping = json_decode($content, true);
            $this->mapping = array_merge($this->mapping, $mapping);
        } else {
            $this->logger->warn("Could not open manual class mapping file: " . $mappingFile);
        }
    }

    /**
     * @param string $className
     * @return string|null
     */
    public function mapM1Class($className)
    {
        if (isset($this->mapping[$className]) && isset($this->mapping[$className]['m2class'])) {
            return $this->mapping[$className]['m2class'];
        } else {
            if (strpos($className, 'Mage_') !== false) {
                $this->logger->warn("Could not map class: " . $className);
            }
            return null;
        }
    }

    /**
     * @param $className
     * @return null|array
     */
    public function getClassMethodMap($className)
    {
        if (isset($this->mapping[$className]) && isset($this->mapping[$className]['methods'])) {
            return $this->mapping[$className]['methods'];
        } else {
            if (strpos($className, 'Mage_') !== false) {
                $this->logger->warn("Could not get class method map: " . $className);
            }
            return null;
        }
    }
}
