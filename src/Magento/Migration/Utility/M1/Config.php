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
     * @param string $configFileContent
     */
    public function __construct($configFileContent)
    {
        $this->config = simplexml_load_string($configFileContent);
    }

    /**
     * @return array
     */
    public function getTableAliases()
    {
        $tableAliases = [];
        $tables = $this->config->xpath('/config/global/models//entities/*/table');
        if (is_array($tables)) {
            foreach ($tables as $tableAlias) {
                /** @var \SimpleXMLElement $tableAlias */
                $tableAliasName = (string)$tableAlias;
                $entityName = current($tableAlias->xpath(".."))->getName();
                $resourceModelName = current(current($tableAlias->xpath("../../../.."))->xpath('//resourceModel'));
                if ($resourceModelName instanceof \SimpleXMLElement) {
                    $modelName = current($resourceModelName->xpath('..'))->getName();
                    $tableAliases[$modelName][$entityName] = $tableAliasName;
                } else {
                    $modelName = str_replace('_resource', '', current($tableAlias->xpath("../../.."))->getName());
                    $tableAliases[$modelName][$entityName] = $tableAliasName;

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
            foreach ($this->config->global->{$type} as $aliases) {
                /**
                 * @var \SimpleXMLElement $modelClass
                 */
                foreach ($aliases as $alias => $aliasClass) {
                    if (!empty($aliasClass->class)) {
                        $result[$alias] = (string)$aliasClass->class;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        $childrens = $this->config->modules->children();
        return $childrens[0]->getName();
    }
}
