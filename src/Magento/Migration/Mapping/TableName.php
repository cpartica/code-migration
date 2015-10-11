<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Mapping;

class TableName
{
    protected $mapping;

    public function __construct()
    {
        $mappingFile = BP . '/mapping/table_names_mapping.json';
        if (file_exists($mappingFile)) {
            $content = file_get_contents($mappingFile);

            $this->mapping = json_decode($content, true);
        } else {
            $this->mapping = [];
        }
    }

    public function mapTableName($tableName, $entity)
    {
        if (isset($this->mapping[$tableName]) && isset($this->mapping[$tableName][$entity])) {
            return $this->mapping[$tableName][$entity];
        } else {
            return null;
        }
    }
}
