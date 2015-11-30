<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Mapping;

class Alias
{
    /**
     * @var array
     */
    protected $mapping = [];

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
     * @param \Magento\Migration\Logger\Logger $logger
     * @param Context $context
     */
    public function __construct(\Magento\Migration\Logger\Logger $logger, Context $context)
    {
        $this->logger = $logger;
        $this->context = $context;
    }

    /**
     * Map alias of specified type to class prefix. The possible types are helper, block and model
     *
     * @param string $alias
     * @param string $type
     * @return string|null
     */
    public function mapAlias($alias, $type)
    {
        if (empty($this->mapping)) {
            $this->getAllMapping();
        }
        if (isset($this->mapping[$type]) && isset($this->mapping[$type][$alias])) {
            if (is_array($this->mapping[$type][$alias])) {
                return end($this->mapping[$type][$alias]);
            } else {
                return $this->mapping[$type][$alias];
            }
        } else {
            $this->logger->warn("Could not map alias " . $alias . ' of type ' . $type);
            return null;
        }
    }

    /**
     * Populate alias map
     *
     * @return array
     */
    public function getAllMapping()
    {
        if (empty($this->mapping)) {
            if ($this->context->getM1BaseDir()) {
                $this->processMappingFromM1Path();
            } else {
                $this->processMappingFromJson();

            }
            if ($this->context->getm1StructureConvertedDir()) {
                $this->processMappingFromM1PathConverted();
            }
        }
        return $this->mapping;
    }

    /**
     * @return void
     */
    private function processMappingFromM1PathConverted()
    {
        //generate mapping based on magento configuration
        $aliases = $this->getAliasesFromFiles(
            glob($this->context->getm1StructureConvertedDir() . '/app/code/*/*/etc/config.xml')
        );
        $this->mapping = array_replace_recursive($this->mapping, $aliases);
    }

    /**
     * @return void
     */
    private function processMappingFromM1Path()
    {
        //generate mapping based on magento configuration
        $aliases = $this->getAliasesFromFiles(glob($this->context->getM1BaseDir() . '/app/code/*/*/*/etc/config.xml'));
        //add default
        $types = array_keys($aliases);
        $coreModules = glob($this->context->getM1BaseDir() . '/app/code/core/Mage/*');
        foreach ($coreModules as $coreModulePath) {
            $defaultAlias = lcfirst(basename($coreModulePath));
            foreach ($types as $type) {
                if (!isset($aliases[$type][$defaultAlias])) {
                    $alias = 'mage ' . $defaultAlias . ' ' . $type;
                    $alias = str_replace(' ', '_', ucwords($alias));
                    $aliases[$type][$defaultAlias] = $alias;
                }
            }
        }
        $this->mapping = array_replace_recursive($this->mapping, $aliases);
    }

    /**
     * @return void
     */
    private function processMappingFromJson()
    {
        //use included mapping for magento core module
        $path = BP . '/mapping/aliases*.json';
        $mappingFiles = glob($path);
        foreach ($mappingFiles as $mappingFile) {
            $content = file_get_contents($mappingFile);
            $mapping = json_decode($content, true);
            $this->mapping = array_replace_recursive($this->mapping, $mapping);
        }
    }

    /**
     * @param string[] $configFiles
     * @return array
     */
    private function getAliasesFromFiles($configFiles)
    {
        $aliases = [
            'helper' => [],
            'block' => [],
            'model' => [],
        ];
        $types = array_keys($aliases);
        foreach ($configFiles as $configFile) {
            $configFileContent = file_get_contents($configFile);
            $config = new \Magento\Migration\Utility\M1\Config($configFileContent, $this->logger);
            foreach ($types as $type) {
                $aliasesForType = $config->getAliases($type . 's');
                $aliases[$type] = array_replace($aliases[$type], $aliasesForType);
            }
        }
        return $aliases;
    }
}
