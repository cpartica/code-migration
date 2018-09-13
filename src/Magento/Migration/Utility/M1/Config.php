<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Utility\M1;

class Config
{
    /**
     * @var \SimpleXMLElement
     */
    protected $config;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @param string $configFileContent
     * @param \Magento\Migration\Logger\Logger $logger
     */
    public function __construct(
        $configFileContent,
        \Magento\Migration\Logger\Logger $logger
    ) {
        $this->logger = $logger;
        $this->config = simplexml_load_string($configFileContent);
        if (!$this->config instanceof \SimpleXMLElement) {
            $this->logger->warn($configFileContent . ' is not a valid xml file or couldn\'t be loaded');
        }
    }

    /**
     * @return array
     */
    public function getTableAliases()
    {
        $tableAliases = [];
        if ($this->config instanceof \SimpleXMLElement) {
            $tables = $this->config->xpath('/config/global/models//entities/*/table');
            if (is_array($tables)) {
                foreach ($tables as $tableAlias) {
                    if ($tableAlias instanceof \SimpleXMLElement) {
                        /** @var \SimpleXMLElement $tableAlias */
                        $tableAliasName = (string)$tableAlias;
                        $entityName = current($tableAlias->xpath(".."))->getName();
                        $resourceModelName = current(
                            current($tableAlias->xpath("../../../.."))->xpath('//resourceModel')
                        );
                        if ($resourceModelName instanceof \SimpleXMLElement) {
                            $modelName = current($resourceModelName->xpath('..'))->getName();
                            $tableAliases[$modelName][$entityName] = $tableAliasName;
                        } else {
                            $modelName = str_replace(
                                '_resource',
                                '',
                                current($tableAlias->xpath("../../.."))->getName()
                            );
                            $tableAliases[$modelName][$entityName] = $tableAliasName;

                        }
                    }
                }
            }
        }
        return $tableAliases;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getAliases($type)
    {
        $result = [];
        if (!empty($this->config->global->{$type})) {
            foreach ($this->config->global->{$type}->children() as $alias => $node) {
                if (!empty($node->class)) {
                    $result[$alias] = (string)$node->class;
                }
            }
        }
        return $result;
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getResourceModelAliases()
    {
        $result = [];
        if (!empty($this->config->global->models)) {
            foreach ($this->config->global->models->children() as $alias => $node) {
                $aliasClass = $this->dereferenceResourceModelAlias($alias);
                if ($aliasClass) {
                    $result[$alias] = $aliasClass;
                }
            }
        }
        return $result;
    }

    /**
     * @param string $alias
     * @return null|string
     */
    protected function dereferenceResourceModelAlias($alias)
    {
        if (!empty($this->config->global->models->{$alias}->resourceModel)) {
            $classNodeName = (string)$this->config->global->models->{$alias}->resourceModel;
            if (!empty($this->config->global->models->{$classNodeName}->class)) {
                $result = (string)$this->config->global->models->{$classNodeName}->class;
                return $result;
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getModuleName()
    {
        /** @var \SimpleXMLElement[] $children */
        $moduleName = null;
        if(is_array($this->config->modules) && count($this->config->modules)) {
          $children = $this->config->modules->children();
          $moduleName = isset($children[0]) ? $children[0]->getName() : null;
        }   
        return $moduleName;
    }
}
