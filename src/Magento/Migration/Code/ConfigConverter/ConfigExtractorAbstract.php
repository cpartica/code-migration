<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter;

use Magento\Migration\Utility\M1\File;
use Magento\Framework\Simplexml\Config;

abstract class ConfigExtractorAbstract implements ConfigExtractorInterface
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface[]
     */
    protected $sections;

    /**
     * @var string
     */
    protected $configFile;

    /**
     * @var \Magento\Framework\Simplexml\ConfigFactory
     */
    protected $configFactory;

    /**
     * @var Config
     */
    protected $xmlConfig;

    /**
     * @param array $sections
     * @param \Magento\Framework\Simplexml\ConfigFactory $configFactory
     */
    public function __construct(
        array $sections,
        \Magento\Framework\Simplexml\ConfigFactory $configFactory
    ) {
        $this->sections = $sections;
        $this->configFactory = $configFactory;
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
     * loops through config files in an M1 xml format
     *
     * @param string $configFile
     * @return ConfigTypeInterface[]|null
     */
    public function getConfigTypes($configFile)
    {
        if (basename($configFile) == $this->filename) {
            $fileHandlers = [];
            foreach ($this->sections as $section) {
                $fileHandlers[] = $section->extract($configFile, $this->getXmlConfig($configFile));
            }
            return $fileHandlers;
        }
        return false;
    }
}
