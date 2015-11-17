<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\EtcConverter;

use Magento\Migration\Utility\M1\File;
use Magento\Framework\Simplexml\Config;
use Magento\Migration\Code\EtcConverter\SystemExtractor\SystemSections;

class SystemExtractor implements EtcExtractorInterface
{
    /**
     * @var SystemSections
     */
    protected $section;

    /**
     * @var string
     */
    protected $etcFile;

    /**
     * @var \Magento\Framework\Simplexml\ConfigFactory
     */
    protected $xmlConfigFactory;

    /**
     * @var Config
     */
    protected $xmlConfig;

    /**
     * @var \Magento\Migration\Mapping\ViewMapping
     */
    protected $viewMapping;

    /**
     * @param array $sections
     * @param \Magento\Framework\Simplexml\ConfigFactory $configFactory
     * @param \Magento\Migration\Mapping\ViewMapping $viewMapping
     * @param SystemSections $section
     */
    public function __construct(
        \Magento\Framework\Simplexml\ConfigFactory $configFactory,
        \Magento\Migration\Mapping\ViewMapping $viewMapping,
        SystemSections $section
    ) {
        $this->section = $section;
        $this->configFactory = $configFactory;
        $this->viewMapping = $viewMapping;
    }

    /**
     * @param string $etcFile
     * @return $this
     */
    public function setFile($etcFile)
    {
        $this->etcFile = $etcFile;
        $this->xmlConfig = $this->getXmlConfig($etcFile);
    }

    /**
     * gets the parsed simple xml from file
     *
     * @param string $etcFile
     * @return Config
     */
    protected function getXmlConfig($etcFile)
    {
        /** @var Config $xmlConfig */
        $xmlConfig = $this->configFactory->create();
        if ($xmlConfig->loadFile($etcFile)) {
            return $xmlConfig;
        }
        return false;
    }

    /**
     * loops through layout handlers in an M1 layout xml format
     *
     * @return EtcTypeInterface[]|false
     */
    public function getEtcTypes()
    {
        if (basename($this->etcFile) == 'system.xml' && $this->xmlConfig) {
            $fileHandlers = [];
            $fileHandlers[] = $this->section->extract($this->etcFile, $this->xmlConfig);
            return $fileHandlers;
        }
        return false;
    }
}
