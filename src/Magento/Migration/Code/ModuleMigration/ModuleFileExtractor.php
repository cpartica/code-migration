<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ModuleMigration;

use Magento\Migration\Utility\M1\File;
use Magento\Framework\Simplexml\Config;

class ModuleFileExtractor
{
    /**
     * @var array
     */
    protected $configSections = ['frontend', 'adminhtml', 'global', 'default', 'admin'];

    /**
     * @var File
     */
    protected $fileUtil;

    /**
     * @var string
     */
    protected $configFile;

    /**
     * @var \Magento\Framework\Simplexml\ConfigFactory
     */
    protected $xmlConfigFactory;

    /**
     * @var Config
     */
    protected $xmlConfig;

    /**
     * @var string
     */
    protected $moduleNamespace;
    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @param \Magento\Framework\Simplexml\ConfigFactory $configFactory
     * @param \Magento\Migration\Utility\M1\FileFactory $fileUtilFactory
     * @param string $configFile
     */
    public function __construct(
        \Magento\Framework\Simplexml\ConfigFactory $configFactory,
        \Magento\Migration\Utility\M1\FileFactory $fileUtilFactory,
        $configFile = null
    ) {
        $this->configFactory = $configFactory;
        if ($configFile) {
            if (preg_match('/^(.+)\/app\/code\//is', $configFile, $match)) {
                if (count($match) == 2) {
                    $this->fileUtil = $fileUtilFactory->create(['basePath' => $match[1]]);
                }
            }
            $this->configFile = $configFile;
            $this->xmlConfig = $this->getXmlConfig($configFile);
        }
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
     * gets the parsed simple xml from file
     *
     * @param string $configFile
     * @return Config
     */
    protected function getXmlConfig($configFile)
    {
        /** @var Config $xmlConfig */
        $xmlConfig = $this->configFactory->create();
        if ($xmlConfig->loadFile($configFile)) {
            return $xmlConfig;
        }
        return false;
    }

    /**
     * gets the module namespace_name format from config
     *
     * @param Config|null $config
     * @return string
     */
    public function getModuleName($config = null)
    {
        if ($config instanceof Config || $this->xmlConfig) {
            if (!$config) {
                $config = $this->xmlConfig;
            }
            $modulesNode = (array)$config->getNode('modules');
            $name = key($modulesNode);
            $this->parseModuleNameSpace($name);
            return $name;
        }
        return false;
    }

    /**
     * loops through config sections and extract csv translation files
     *
     * @param Config|null $config
     * @return array
     */
    public function getTranslationsFromConfig($config = null)
    {
        $files = [];
        if ($config instanceof Config || $this->xmlConfig) {
            if (!$config) {
                $config = $this->xmlConfig;
            }
            foreach ($this->configSections as $section) {
                $elements = $config->getXpath('/config/' . $section . '/translate//*[contains(text(),\'csv\')]');
                if ($elements) {
                    foreach ($elements as $element) {
                        $files[(string)$element] = (string)$element;
                    }
                }
            }
        }
        return $files;
    }


    /**
     * loops through config sections and layout xml definitions and parse it to find relevant files
     *
     * @param string $zone
     * @param Config $config
     * @return array
     */
    public function getViewFromConfig($zone = 'global', $config = null)
    {
        $files = [];
        if ($config instanceof Config || $this->xmlConfig) {
            if (!$config) {
                $config = $this->xmlConfig;
            }
            $res = $config->getXpath('/config/' . $zone . '/layout//file');
            if ($res) {
                foreach ($res as $file) {
                    $files[(string)$file] = (string)$file;
                }
            }
        }
        return $files;
    }

    /**
     * @param array $files
     * @param string $zone
     * @return array
     */
    public function getViewLayoutXmlFromFiles($files, $zone = 'frontend')
    {
        if (is_array($files)) {
            $base = 'base';
            if ($zone == 'adminhtml') {
                $base = 'default';
            }
            return [$zone . 'Xml' => $this->fileUtil->getFilesFromPath(
                $files,
                $this->fileUtil->getBasePath() . '/app/design/' . $zone . '/' . $base . '/default/layout'
            )];
        }
        return [];
    }

    /**
     * returns the full path of the xml & phtml files defined in the config from layout locations
     *
     * @param array $files
     * @param string $zone
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getViewTemplatesFromFiles($files, $zone = 'frontend')
    {
        if (is_array($files)) {
            $base = 'base';
            if ($zone == 'adminhtml') {
                $base = 'default';
            }
            $phtmlFiles = [];
            $voteArray = [];
            foreach ($files as $xmlfile) {
                /** @var Config $xmlConfig */
                $xmlConfig = $this->configFactory->create();
                $xmlConfig->loadFile($xmlfile);
                $elementsBlocks = $xmlConfig->getXpath('/layout//block[contains(@template,\'.phtml\')]');
                if (!$elementsBlocks) {
                    $elementsBlocks = [];
                }
                $elementsTemplates = $xmlConfig->getXpath(
                    '/layout//action[@method =\'setTemplate\']/template[contains(text(),\'.\')]'
                );
                if (!$elementsTemplates) {
                    $elementsTemplates = [];
                }
                foreach (array_merge($elementsBlocks, $elementsTemplates) as $element) {
                    $attrs = $element->attributes();
                    if (preg_match('/.+\.phtml/', (string)$element)) {
                        $template = (string)$element;
                    } elseif (preg_match('/.+\.phtml/', (string)$attrs['template'])) {
                        $template = (string)$attrs['template'];
                    }
                    $phtmlFiles[$template] = $template;

                    //guess the module's frontend folder where all phtml are (some might not be defined in the XML)
                    if (preg_match('/^([^\/]+)/', $template, $match)) {
                        if (!array_key_exists($match[1], $voteArray)) {
                            $voteArray[$match[1]] = 1;
                        } else {
                            $voteArray[$match[1]] = $voteArray[$match[1]] + 1;
                        }
                    }
                }
            }

            $otherPhtml = [];
            if (count($voteArray) > 0) {
                $voted = array_keys($voteArray, max($voteArray));
                $otherPhtml = $this->fileUtil->getFilesFromPath(
                    ['*.phtml'],
                    $this->fileUtil->getBasePath()
                    . '/app/design/' . $zone . '/' . $base . '/default/template/'
                    . $voted[0]
                );

            }

            $xmlPhtml = $this->fileUtil->getFilesFromPath(
                $phtmlFiles,
                $this->fileUtil->getBasePath() . '/app/design/' . $zone . '/' . $base . '/default/template'
            );
            if (is_array($xmlPhtml) || is_array($otherPhtml)) {
                return [$zone . 'Phtml' => array_unique(array_merge($xmlPhtml, $otherPhtml))];
            }
        }
        return [];
    }

    /**
     * returns the full path of the skin & js files defined in the config from layout locations
     *
     * @param array $files
     * @param string $zone
     * @return array
     */
    public function getSkinJsFromFiles($files, $zone = 'frontend')
    {
        if (is_array($files)) {
            $base = 'base';
            if ($zone == 'adminhtml') {
                $base = 'default';
            }
            $allFiles = [];
            foreach ($files as $xmlfile) {
                /** @var Config $xmlConfig */
                $xmlConfig = $this->configFactory->create();
                $xmlConfig->loadFile($xmlfile);
                $elements = $xmlConfig->getXpath(
                    '/layout//action[starts-with(@method,\'add\')]/*[contains(text(),\'.\')]'
                );
                if ($elements) {
                    foreach ($elements as $element) {
                        $allFiles[(string)$element] = (string)$element;
                    }
                }
            }
            if (count($allFiles)) {
                $skinFiles = $this->fileUtil->getFilesFromPath(
                    $allFiles,
                    $this->fileUtil->getBasePath() . '/skin/' . $zone . '/' . $base . '/default'
                );
                $jsFiles = $this->fileUtil->getFilesFromPath(
                    $allFiles,
                    $this->fileUtil->getBasePath() . '/js'
                );
                $imageFiles = $this->findImageFiles($skinFiles, $zone);
                if (is_array($skinFiles) || is_array($jsFiles)) {
                    return [$zone . 'Web' => array_unique(array_merge($skinFiles, $jsFiles, $imageFiles))];
                }
            }
        }
        return [];
    }

    /**
     * try finding other files from folder package without scanning the xml php and phtml code
     * patterns:
     * [adminhtml|frontend]/[default|base]/default/css/[company]/[module]//*.[imageExt]
     * [adminhtml|frontend]/[default|base]/default/images/[company]/[module]//*.[imageExt]
     * [adminhtml|frontend]/[default|base]/default/images/[module]//*.[imageExt]
     * [adminhtml|frontend]/[default|base]/default/company]/[module]//*.[imageExt]
     * [adminhtml|frontend]/[default|base]/default/[module]//*.[imageExt]
     * [adminhtml|frontend]/[default|base]/default/[module]/images/*.[imageExt]
     * [adminhtml|frontend]/[default|base]/default/css/[module]/images/*.[imageExt]
     *
     * @param array $files
     * @param string $zone
     * @return array;
     */
    protected function findImageFiles($files, $zone = 'frontend')
    {
        if (is_array($files)) {
            $skippedFolder = 'css|images|styles|img|flv|flash|video|js|javascript';
            $parrentFolders = [];
            foreach ($files as $file) {
                $key = basename(dirname($file));
                if (!preg_match('/^(' . $skippedFolder . ')$/', $key)) {
                    $parrentFolders[$key] = $key;
                } else {
                    $key = basename(dirname(dirname($file)));
                    if (!preg_match('/^(' . $skippedFolder . ')$/', $key)) {
                        $parrentFolders[$key] = $key;
                    }
                }
            }

            $base = 'base';
            if ($zone == 'adminhtml') {
                $base = 'default';
            }

            //find parent folder of css files and add look for other files in that folder and for that folder
            $otherFiles = [];
            foreach ($parrentFolders as $folder) {
                $otherFiles = array_merge($this->fileUtil->getFilesFromPath(
                    [
                        $folder . '/*.*',
                        $folder . '/*/*.*',
                        $folder . '/*/*/*.*',
                        $folder . '/*/*/*/*.*',
                    ],
                    $this->fileUtil->getBasePath() . '/skin/' . $zone . '/' . $base . '/default'
                ), $otherFiles);

                $otherFiles = array_merge($this->fileUtil->getFilesFromPath(
                    [
                        $folder . '/*.*',
                        $folder . '/*/*.*',
                        $folder . '/*/*/*.*',
                        $folder . '/*/*/*/*.*',
                    ],
                    $this->fileUtil->getBasePath() . '/js'
                ), $otherFiles);
            }
            return $otherFiles;
        }
        return [];
    }

    /**
     * returns the full path of the CSV files defined in the config from different language locations
     *
     * @param array $files
     * @return array
     */
    public function getTranslationsFromFiles($files)
    {
        if (is_array($files)) {
            return [
                'i18n' => $this->fileUtil->getFilesFromPath($files, $this->fileUtil->getBasePath() . '/app/locale')
            ];
        }
        return [];
    }


    /**
     * returns the full path of model files found in the module folder
     *
     * @param string $folder
     * @param string $codePool
     * @param string $fileExtension
     * @return array
     */
    public function getFromFiles($folder = null, $codePool = 'local', $fileExtension = 'php')
    {
        if ($this->getModuleName() && $folder) {
            return $this->fileUtil->getFilesFromPath(
                ['*.' . $fileExtension],
                $this->fileUtil->getBasePath() . '/app/code/' . $codePool . '/' .
                $this->moduleNamespace . '/' . $this->moduleName . '/' . $folder
            );
        }
        return [];
    }

    /**
     * returns the full path of model files found in the module folder
     *
     * @param string $codePool
     * @return array
     */
    public function getModelsFromFiles($codePool = 'local')
    {
        return ['Model' => $this->getFromFiles('Model', $codePool)];
    }

    /**
     * returns the full path of helper files found found in the module folder
     *
     * @param string $codePool
     * @return array
     */
    public function getHelpersFromFiles($codePool = 'local')
    {
        return ['Helper' => $this->getFromFiles('Helper', $codePool)];
    }

    /**
     * returns the full path of block files found found in the module folder
     *
     * @param string $codePool
     * @return array
     */
    public function getBlockFromFiles($codePool = 'local')
    {
        return ['Block' => $this->getFromFiles('Block', $codePool)];
    }

    /**
     * returns the full path of controller files found found in the module folder
     *
     * @param string $codePool
     * @return array
     */
    public function getControllersFromFiles($codePool = 'local')
    {
        return ['Controller' => $this->getFromFiles('controllers', $codePool)];
    }

    /**
     * returns the full path of etc files found found in the module folder
     *
     * @param string $codePool
     * @return array
     */
    public function getEtcFromFiles($codePool = 'local')
    {
        return ['etc' => $this->getFromFiles('etc', $codePool, 'xml')];
    }
}
