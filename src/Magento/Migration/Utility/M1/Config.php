<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Utility\M1;

class Config
{
    /**
     * @var string
     */
    protected $baseDir;

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

    public function getHelperAliases()
    {
        $helperAliases = [];
        if (!empty($this->config->global->helpers)) {
            foreach ($this->config->global->helpers as $helperAlias) {
                /**
                 * @var \SimpleXMLElement $helperClass
                 */
                foreach ($helperAlias as $alias => $helperClass) {
                    $helperAliases[$alias] = (string)$helperClass->class;
                }
            }
        }
        return $helperAliases;
    }

    public function getModelAliases()
    {
        $modelAliases = [];
        if (!empty($this->config->global->model)) {
            foreach ($this->config->global->model as $modelAlias) {
                /**
                 * @var \SimpleXMLElement $modelClass
                 */
                foreach ($modelAlias as $alias => $modelClass) {
                    $modelAliases[$alias] = (string)$modelClass->class;
                }
            }
        }
        return $modelAliases;
    }

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

    public function getModuleName()
    {
        $childrens = $this->config->modules->children();
        return $childrens[0]->getName();
    }
}
