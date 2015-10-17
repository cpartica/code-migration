<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Utility;

class ClassMapGenerator
{
    /**
     * @var string
     */
    protected $obsoleteClassListPath;

    /**
     * @var array
     */
    protected $obsoleteClassMap = null;

    /**
     * @var array
     */
    protected $unmapped = [];

    /**
     * @var \Magento\Migration\Mapping\Module
     */
    protected $moduleMapper;

    public function __construct(\Magento\Migration\Mapping\Module $moduleMap)
    {
        $this->moduleMapper = $moduleMap;
        $this->obsoleteClassListPath = BP . '/utils/obsolete_classes.php';
    }

    /**
     * @param string $className
     * @return string
     */
    public function mapM1CoreClass($className)
    {
        if ($this->obsoleteClassMap === null) {
            $this->obsoleteClassMap = [];
            $this->unmapped = [];
            $this->populateList($this->obsoleteClassMap);
        }

        if (isset($this->obsoleteClassMap[$className])) {
            return '\\' . $this->obsoleteClassMap[$className];
        }

        $parts = explode('_', $className);

        $moduleName = implode('_', [$parts[0], $parts[1]]);
        $mappedModuleName = $this->moduleMapper->mapModule($moduleName);
        if ($mappedModuleName) {
            $mappedParts = explode('_', $mappedModuleName);
            $parts[0] = $mappedParts[0];
            $parts[1] = $mappedParts[1];

            //before mapping adminhtml blocks, check the obsolete class map first
            $m2ClassName = implode('\\', $parts);
            if (isset($this->obsoleteClassMap[$m2ClassName])) {
                return '\\' . $this->obsoleteClassMap[$m2ClassName];
            }
            if (count($parts) > 3 && $parts[0] == 'Magento' && $parts[1] == 'Backend') {
                $parts = $this->mapAdminhtml($parts);
            }
            if (count($parts) > 4 && $parts[0] == 'Magento' && $parts[2] == 'Model' && $parts[3] == 'Resource') {
                $parts[3] = 'ResourceModel';
            }
        } elseif ($parts[0] == 'Varien') {
            array_shift($parts);
            $parts = array_merge(['Magento', 'Framework'], $parts);
        } else {
            $parts[0] = 'Magento';
        }

        $parts = $this->fixReservedWords($parts);

        $m2ClassName = implode('\\', $parts);
        if (isset($this->obsoleteClassMap[$m2ClassName])) {
            return '\\' . $this->obsoleteClassMap[$m2ClassName];
        }
        return '\\' . $m2ClassName;
    }

    /**
     * @param array $parts
     * @return array
     */
    protected function fixReservedWords(array $parts)
    {
        $numParts = count($parts);
        $lastPart = $parts[$numParts - 1];

        if ($lastPart == 'Abstract' || $lastPart == 'Default') {
            $parts[$numParts - 1] = $lastPart . $parts[$numParts - 2];
        } elseif ($lastPart == 'Interface') {
            $parts[$numParts - 1] = $parts[$numParts - 2] . 'Interface';
        }
        return $parts;
    }

    /**
     * @param array $parts
     * @return array
     */
    protected function mapAdminhtml($parts)
    {
        if ($parts[2] == 'Block' || $parts[2] == 'controllers') {
            $module = $parts[3];
            if (count($parts) == 4) {
                $rest = array_slice($parts, 3);
            } else {
                $rest = array_slice($parts, 4);
            }
            if ($parts[2] == 'Block') {
                return array_merge(['Magento', $module, 'Block', 'Adminhtml'], $rest);
            } else {
                return array_merge(['Magento', $module, 'Controller', 'Adminhtml'], $rest);
            }
        } else {
            return $parts;
        }
    }

    /**
     * @param array $list
     * @return void
     */
    protected function populateList(array &$list)
    {
        foreach ($this->readList($this->obsoleteClassListPath) as $row) {
            if (isset($row[1])) {
                $list[$row[0]] = $row[1];
            } else {
                $list[$row[0]] = 'Obsolete Class';
            }
        }
    }
    /**
     * Isolate including a file into a method to reduce scope
     *
     * @param string $file
     * @return array
     */
    protected function readList($file)
    {
        return include $file;
    }
}
