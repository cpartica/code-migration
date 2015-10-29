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

    /**
     * @param \Magento\Migration\Logger\Logger $logger
     */
    public function __construct(\Magento\Migration\Logger\Logger $logger)
    {
        $this->logger = $logger;

        $this->mapping = [];
        $mappingFiles = glob(BP . '/mapping/class_mapping*.json');
        foreach ($mappingFiles as $mappingFile) {
            $content = file_get_contents($mappingFile);
            $classMappings = json_decode($content, true);
            $this->mapping = array_merge($this->mapping, $classMappings);
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
     * @param string $className
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

    /**
     * get all class mapping array
     *
     * @return mixed[]
     */
    public function getAllClassMapping()
    {
        return $this->mapping;
    }
}
