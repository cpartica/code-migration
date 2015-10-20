<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Utility\M2;

class File extends \Magento\Migration\Utility\File
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @param string $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @return array
     */
    public function getClasses()
    {
        $mageDir = $this->basePath . '/app/code/Magento';
        $libDir = $this->basePath . '/lib/internal/Magento';
        $codeDirLen = strlen($this->basePath . '/app/code/');
        $libDirLen = strlen($this->basePath . '/lib/internal/');
        $files = $this->getFiles([$mageDir, $libDir], '*.php', true);
        $classes = [];
        foreach ($files as $file) {
            if (strpos($file, 'sql') || strpos($file, 'data')) {
                continue;
            }
            if (strpos($file, 'app/code')) {
                $className = substr($file, $codeDirLen, -4);
            } else {
                $className = substr($file, $libDirLen, -4);
            }
            $className = str_replace(DIRECTORY_SEPARATOR, '\\', $className);
            $classes[$className] = $file;
        }
        return $classes;
    }

    /**
     * Check whether a given class exists in Magento 2
     *
     * @param string $className
     * @return bool
     */
    public function isM2Class($className)
    {
        $relativePath = trim($className, '\\');
        $relativePath = str_replace('\\', '/', $relativePath);
        $relativePath .= '.php';
        return file_exists($this->basePath . '/app/code/' . $relativePath)
        || file_exists($this->basePath . '/lib/internal/' . $relativePath);
    }
}
