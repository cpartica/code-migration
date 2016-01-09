<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Utility\M1;

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
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }
    /**
     * @return array
     */
    public function getClasses()
    {
        $mageDir = $this->basePath . '/app/code/core/Mage';
        $enterpriseDir = $this->basePath . '/app/code/core/Enterprise';
        $codeDirLen = strlen($this->basePath . '/app/code/core/');
        $files = $this->getFiles([$mageDir, $enterpriseDir], '*.php', true);
        $classes = [];
        foreach ($files as $file) {
            if (strpos($file, '/sql/') || strpos($file, '/data/')) {
                continue;
            }
            $className = substr($file, $codeDirLen, -4);
            $className = str_replace(DIRECTORY_SEPARATOR, '_', $className);
            $classes[$className] = $file;
        }

        //add lib files
        $libMageDir = $this->basePath . '/lib/Mage';
        $libMagentoDir = $this->basePath . '/lib/Magento';
        $libVarienDir = $this->basePath . '/lib/Varien';
        $libDirLen = strlen($this->basePath . '/lib/');

        $files = $this->getFiles([$libMageDir, $libMagentoDir, $libVarienDir], '*.php', true);
        foreach ($files as $file) {
            $className = substr($file, $libDirLen, -4);
            $className = str_replace(DIRECTORY_SEPARATOR, '_', $className);
            $classes[$className] = $file;
        }

        return $classes;
    }

    /**
     * Retrieve all files in folders and sub-folders that match pattern (glob syntax)
     *
     * @param array $dirPatterns
     * @param string $fileNamePattern
     * @param bool $recursive
     * @return array
     */
    public function getFiles(array $dirPatterns, $fileNamePattern, $recursive = true)
    {
        $result = [];
        foreach ($dirPatterns as $oneDirPattern) {
            $entriesInDir = glob("{$oneDirPattern}/{$fileNamePattern}", GLOB_NOSORT | GLOB_BRACE);
            $subDirs = glob("{$oneDirPattern}/*", GLOB_ONLYDIR | GLOB_NOSORT | GLOB_BRACE);
            $filesInDir = array_diff($entriesInDir, $subDirs);

            if ($recursive) {
                $filesInSubDir = $this->getFiles($subDirs, $fileNamePattern);
                $result = array_merge($result, $filesInDir, $filesInSubDir);
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getModulesConfigFiles()
    {
        $supportFolders = ['local', 'community'];
        $searchFolders = [];
        foreach ($supportFolders as $folder) {
            $searchFolders[$folder] = $this->basePath . '/app/code/' . $folder;
        }
        $files = $this->getFiles($searchFolders, 'config.xml', true);
        $configs = [];
        foreach ($files as $file) {
            foreach ($searchFolders as $supportFolder => $folder) {
                if (preg_match('/' . str_replace('/', '\/', $folder). '\/([^\/]+)\/([^\/]+)/', $file, $match)) {
                    if (count($match) == 3) {
                        $configs[$supportFolder][$match[1]][$match[2]] = $file;
                    }
                }
            }
        }
        return $configs;
    }

    /**
     * @param string[] $maskArray
     * @param string|null $path
     * @return array
     */
    public function getFilesFromPath($maskArray, $path = null)
    {
        $filesArray = [];
        if (is_array($maskArray)) {
            foreach ($maskArray as $mask) {
                $files = $this->getFiles([$path ? $path : $this->basePath], $mask, true);
                $filesArray = array_merge($filesArray, $files);
            }
        }
        return $filesArray;
    }
}
