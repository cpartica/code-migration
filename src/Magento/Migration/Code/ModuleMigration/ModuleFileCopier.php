<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ModuleMigration;

use Magento\Migration\Utility\M1\File;

class ModuleFileCopier
{
    const FILE_PERMS = 0755;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var string
     */
    protected $moduleNamespace;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var string
     */
    protected $outputFolder;

    /**
     * @param string $outputFolder
     * @param string $module
     * @param \Magento\Framework\Filesystem\Driver\File $file
     */
    public function __construct(
        $outputFolder,
        $module,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        $this->file = $file;
        if ($this->file->isExists($outputFolder)) {
            $this->outputFolder = $outputFolder;
            $this->parseModuleNameSpace($module);
        }
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function mkpath($path)
    {
        if ($this->file->createDirectory($path, self::FILE_PERMS) || $this->file->isExists($path)) {
            return true;
        }
        return (
            $this->mkpath($this->file->getParentDirectory($path)) &&
            $this->file->createDirectory($path, self::FILE_PERMS)
        );
    }

    /**
     * @param string $module
     * @return $this|false
     */
    protected function parseModuleNameSpace($module)
    {
        $moduleNamesArray = explode('_', $module);
        if (count($moduleNamesArray) == 2) {
            $this->moduleNamespace = $moduleNamesArray[0];
            $this->moduleName = $moduleNamesArray[1];
            return $this;
        }
        return false;
    }

    /**
     * @return bool|string
     */
    public function createM2ModuleFolder()
    {
        if ($this->moduleName && $this->moduleNamespace && $this->outputFolder) {
            $folder = $this->outputFolder . '/app/code/' . $this->moduleNamespace;
            if (!$this->file->isExists($folder)) {
                $this->mkpath($folder);
            }
            $folder = $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' . $this->moduleName;
            if (!$this->file->isExists($folder)) {
                $this->mkpath($folder);
            }

            return $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' . $this->moduleName;
        }
        return false;
    }

    /**
     * @param string[] $files
     * @param string $contentFolder
     * @return bool
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function copyM2Files($files, $contentFolder = 'Model')
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                switch ($contentFolder) {
                    case 'i18n':
                        if (preg_match(
                            '/app\/locale\/([^\/]+)\/(.+)\.csv$/is',
                            $file,
                            $match
                        )) {
                            if (count($match) == 3) {
                                $fileToCopy = $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' .
                                    $this->moduleName . '/' . $contentFolder . '/' . $match[1] . '.csv';
                                if (!$this->file->isExists($this->file->getParentDirectory($fileToCopy))) {
                                    $this->mkpath($this->file->getParentDirectory($fileToCopy));
                                }
                                $this->file->copy($file, $fileToCopy);
                            }
                        }
                        break;
                    case 'frontendWeb':
                        if (preg_match(
                            '/frontend\/base\/default\/([^\/]+)\/(.+\..+)$/is',
                            $file,
                            $match
                        )) {
                            if (count($match) == 3) {
                                $fileToCopy = $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' .
                                    $this->moduleName . '/view/frontend/web/' . $match[1] . '/' . $match[2];
                                if (!$this->file->isExists($this->file->getParentDirectory($fileToCopy))) {
                                    $this->mkpath($this->file->getParentDirectory($fileToCopy));
                                }
                                $this->file->copy($file, $fileToCopy);
                            }
                        } elseif (preg_match(
                            '/\/js\/([^\/]+)\/(.+\.js)$/is',
                            $file,
                            $match
                        )) {
                            if (count($match) == 3) {
                                $fileToCopy = $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' .
                                    $this->moduleName . '/view/base/web/js/' . $match[1] . '/' . $match[2];
                                if (!$this->file->isExists($this->file->getParentDirectory($fileToCopy))) {
                                    $this->mkpath($this->file->getParentDirectory($fileToCopy));
                                }
                                $this->file->copy($file, $fileToCopy);
                            }
                        }
                        break;
                    case 'frontendXml':
                        $fileToCopy = $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' .
                            $this->moduleName . '/view/frontend/layout/' . basename($file);
                        if (!$this->file->isExists($this->file->getParentDirectory($fileToCopy))) {
                            $this->mkpath($this->file->getParentDirectory($fileToCopy));
                        }
                        $this->file->copy($file, $fileToCopy);
                        break;
                    case 'frontendPhtml':
                        if (preg_match(
                            '/frontend\/base\/default\/template\/([^\/]+)\/(.+\.phtml)$/is',
                            $file,
                            $match
                        )) {
                            if (count($match) == 3) {
                                $fileToCopy = $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' .
                                    $this->moduleName . '/view/frontend/templates/' . $match[1] . '/' . $match[2];
                                if (!$this->file->isExists($this->file->getParentDirectory($fileToCopy))) {
                                    $this->mkpath($this->file->getParentDirectory($fileToCopy));
                                }
                                $this->file->copy($file, $fileToCopy);
                            }
                        }
                        break;
                    case 'adminhtmlWeb':
                        if (preg_match(
                            '/adminhtml\/default\/default\/([^\/]+)\/(.+\..+)$/is',
                            $file,
                            $match
                        )) {
                            if (count($match) == 3) {
                                $fileToCopy = $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' .
                                    $this->moduleName . '/view/adminhtml/web/' . $match[1] . '/' . $match[2];
                                if (!$this->file->isExists($this->file->getParentDirectory($fileToCopy))) {
                                    $this->mkpath($this->file->getParentDirectory($fileToCopy));
                                }
                                $this->file->copy($file, $fileToCopy);
                            }
                        } elseif (preg_match(
                            '/\/js\/([^\/]+)\/(.+\.js)$/is',
                            $file,
                            $match
                        )) {
                            if (count($match) == 3) {
                                $fileToCopy = $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' .
                                    $this->moduleName . '/view/base/web/js/' . $match[1] . '/' . $match[2];
                                if (!$this->file->isExists($this->file->getParentDirectory($fileToCopy))) {
                                    $this->mkpath($this->file->getParentDirectory($fileToCopy));
                                }
                                $this->file->copy($file, $fileToCopy);
                            }
                        }
                        break;
                    case 'adminhtmlXml':
                        $fileToCopy = $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' .
                            $this->moduleName . '/view/adminhtml/layout/' . basename($file);
                        if (!$this->file->isExists($this->file->getParentDirectory($fileToCopy))) {
                            $this->mkpath($this->file->getParentDirectory($fileToCopy));
                        }
                        $this->file->copy($file, $fileToCopy);
                        break;
                    case 'adminhtmlPhtml':
                        if (preg_match(
                            '/adminhtml\/default\/default\/template\/([^\/]+)\/(.+\.phtml)$/is',
                            $file,
                            $match
                        )) {
                            if (count($match) == 3) {
                                $fileToCopy = $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' .
                                    $this->moduleName . '/view/adminhtml/templates/' . $match[1] . '/' . $match[2];
                                if (!$this->file->isExists($this->file->getParentDirectory($fileToCopy))) {
                                    $this->mkpath($this->file->getParentDirectory($fileToCopy));
                                }
                                $this->file->copy($file, $fileToCopy);
                            }
                        }
                        break;
                    case 'Controller':
                        if (preg_match(
                            '/' . $this->moduleNamespace . '\/' . $this->moduleName . '\/' .
                            'controllers' . '\/(.+)$/is',
                            $file,
                            $match
                        )) {
                            if (count($match) == 2) {
                                $fileToCopy = $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' .
                                    $this->moduleName . '/' . $contentFolder . '/' . $match[1];
                                if (!$this->file->isExists($this->file->getParentDirectory($fileToCopy))) {
                                    $this->mkpath($this->file->getParentDirectory($fileToCopy));
                                }
                                $this->file->copy($file, $fileToCopy);
                            }
                        }
                        break;
                    default:
                        if (preg_match(
                            '/' . $this->moduleNamespace . '\/' . $this->moduleName . '\/' .
                            $contentFolder . '\/(.+)$/is',
                            $file,
                            $match
                        )) {
                            if (count($match) == 2) {
                                $fileToCopy = $this->outputFolder . '/app/code/' . $this->moduleNamespace . '/' .
                                    $this->moduleName . '/' . $contentFolder . '/' . $match[1];
                                if (!$this->file->isExists($this->file->getParentDirectory($fileToCopy))) {
                                    $this->mkpath($this->file->getParentDirectory($fileToCopy));
                                }
                                $this->file->copy($file, $fileToCopy);
                            }
                        }
                }
            }
        }
        return false;
    }
}
