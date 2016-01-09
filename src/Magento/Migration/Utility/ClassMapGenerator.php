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

    /**
     * @param \Magento\Migration\Mapping\Module $moduleMap
     */
    public function __construct(\Magento\Migration\Mapping\Module $moduleMap)
    {
        $this->moduleMapper = $moduleMap;
        $this->obsoleteClassListPath = BP . '/utils/obsolete_classes.php';
    }

    /**
     * @param string $className
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function mapM1Class($className)
    {
        if ($this->obsoleteClassMap === null) {
            $this->obsoleteClassMap = [];
            $this->unmapped = [];
            $this->populateList($this->obsoleteClassMap);
        }

        $m2ClassName = $this->suggestObsoleteClassReplacement($className);
        if ($m2ClassName) {
            return $m2ClassName;
        }

        $parts = explode('_', $className);

        $parts = $this->fixModuleName($parts);
        $parts = $this->fixResourceModelClass($parts, 'Mysql4', 'Resource');
        $parts = $this->fixReservedWords($parts);

        //before mapping adminhtml blocks, check the obsolete class map first
        $m2ClassName = $this->suggestObsoleteClassReplacement($this->renderNamespaceClass($parts));
        if ($m2ClassName) {
            return $m2ClassName;
        }

        $parts = $this->fixAdminhtmlClass($parts);
        $parts = $this->fixResourceModelClass($parts);
        $parts = $this->fixLibraryClass($parts);

        $m2ClassName = $this->suggestObsoleteClassReplacement($this->renderNamespaceClass($parts));
        if ($m2ClassName) {
            return $m2ClassName;
        }
        return $this->renderNamespaceClass($parts);
    }

    /**
     * @param string $className
     * @return null|string
     */
    protected function suggestObsoleteClassReplacement($className)
    {
        $className = ltrim($className, '\\');
        if (isset($this->obsoleteClassMap[$className])) {
            return '\\' . $this->obsoleteClassMap[$className];
        }
        return null;
    }

    /**
     * @param array $parts
     * @return string
     */
    protected function renderNamespaceClass(array $parts)
    {
        return '\\' . implode('\\', $parts);
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
    protected function fixModuleName(array $parts)
    {
        if (count($parts) > 1) {
            $moduleName = $parts[0] . '_' . $parts[1];
            $mappedModuleName = $this->moduleMapper->mapModule($moduleName);
            if ($mappedModuleName) {
                list($parts[0], $parts[1]) = explode('_', $mappedModuleName, 2);
            } else if ($parts[0] == 'Mage' || $parts[0] == 'Enterprise') {
                $parts[0] = 'Magento';
            }
        }
        return $parts;
    }

    /**
     * @param array $parts
     * @param string $search
     * @param string $replace
     * @return array
     */
    protected function fixResourceModelClass(array $parts, $search = 'Resource', $replace = 'ResourceModel')
    {
        if (count($parts) > 4 && $parts[0] == 'Magento' && $parts[2] == 'Model' && $parts[3] == $search) {
            $parts[3] = $replace;
        }
        return $parts;
    }

    /**
     * @param array $parts
     * @return array
     */
    protected function fixLibraryClass(array $parts)
    {
        if (count($parts) > 1 && $parts[0] == 'Varien') {
            array_splice($parts, 0, 1, ['Magento', 'Framework']);
        }
        return $parts;
    }

    /**
     * @param array $parts
     * @return array
     */
    protected function fixAdminhtmlClass(array $parts)
    {
        if (count($parts) > 3 && $parts[0] == 'Magento' && $parts[1] == 'Backend') {
            $parts = $this->mapAdminhtml($parts);
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
