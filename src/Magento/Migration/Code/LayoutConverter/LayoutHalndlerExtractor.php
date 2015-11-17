<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\LayoutConverter;

use Magento\Migration\Utility\M1\File;
use Magento\Framework\Simplexml\Config;

class LayoutHalndlerExtractor
{
    /**
     * @var string
     */
    protected $layoutHandlerFile;

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
     * @param \Magento\Framework\Simplexml\ConfigFactory $configFactory
     * @param \Magento\Migration\Mapping\ViewMapping $viewMapping
     * @param string $layoutHandlerFile
     */
    public function __construct(
        \Magento\Framework\Simplexml\ConfigFactory $configFactory,
        \Magento\Migration\Mapping\ViewMapping $viewMapping,
        $layoutHandlerFile
    ) {
        $this->configFactory = $configFactory;
        $this->layoutHandlerFile = $layoutHandlerFile;
        $this->viewMapping = $viewMapping;
        $this->xmlConfig = $this->getXmlConfig($layoutHandlerFile);
    }

    /**
     * gets the parsed simple xml from file
     *
     * @param string $layoutHandlerFile
     * @return Config
     */
    protected function getXmlConfig($layoutHandlerFile)
    {
        /** @var Config $xmlConfig */
        $xmlConfig = $this->configFactory->create();
        if ($xmlConfig->loadFile($layoutHandlerFile)) {
            return $xmlConfig;
        }
        return false;
    }

    /**
     * loops through layout handlers in an M1 layout xml format
     *
     * @return array|false
     */
    public function getLayoutHandlers()
    {
        $handlers = [];
        if ($this->xmlConfig) {
            $array = $this->xmlConfig->getXpath('/layout');
            if (is_array($array)) {
                foreach (current($array) as $layoutHandlerNode) {
                    /** @var \Magento\Framework\Simplexml\Element $layoutHandlerNode */
                    $name = strtolower($layoutHandlerNode->getName());
                    $type= preg_match('/adminhtml/is', $this->layoutHandlerFile) ?
                        \Magento\Migration\Mapping\ViewMapping::ADMINHTML :
                        \Magento\Migration\Mapping\ViewMapping::FRONTEND;
                    //look for a mapping of this layout to M2
                    if ($newName = $this->viewMapping->mapLayoutHandler($name, $type)) {
                        if ($newName != 'obsolete') {
                            $name = $newName;
                        } else {
                            $name = $name . "_OBSOLETE";
                        }
                    }
                    $fileName = dirname($this->layoutHandlerFile) . '/' . $name . '.xml';
                    $handlers[$fileName] = $layoutHandlerNode->asNiceXml();
                }
            } else {
                return false;
            }
        }
        return $handlers;
    }
}
