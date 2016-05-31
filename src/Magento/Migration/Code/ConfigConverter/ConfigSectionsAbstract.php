<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter;

/**
 * Class ConfigSectionsAbstract
 * @package Magento\Migration\Code\ConfigConverter
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 *
 */
abstract class ConfigSectionsAbstract extends ConfigType implements ConfigSectionsInterface
{

    /**
     * @var string
     */
    protected $xmlSchema;

    /**
     * @var string[]
     */
    protected $xsls = [];

    /**
     * @var string
     */
    protected $tagName = 'job';

    /**
     * @var string
     */
    protected $fileName;
    /**
     * @var array
     */
    protected $locations = [];

    /**
     * @var \Magento\Migration\Code\ConfigConverter\ConfigTypeFactory
     */
    protected $configTypeInterfaceFactory;

    /**
     * @param \Magento\Migration\Code\ConfigConverter\ConfigTypeFactory $configTypeInterfaceFactory
     */
    public function __construct(
        \Magento\Migration\Code\ConfigConverter\ConfigTypeFactory $configTypeInterfaceFactory
    ) {
        $this->configTypeInterfaceFactory = $configTypeInterfaceFactory;
    }

    /**
     * @param string $file
     * @param \Magento\Framework\Simplexml\Config $xmlConfig
     * @return \Magento\Migration\Code\ConfigConverter\ConfigTypeInterface[]|false
     */
    public function extract($file, $xmlConfig)
    {
        if ($this->fileName && $xmlConfig && $file) {
            $handlers = [];
            foreach ($this->locations as $location => $folder) {
                if (is_numeric($location)) {
                    $nodeArray = $xmlConfig->getXpath('/*/*');
                } else {
                    $nodeArray = $xmlConfig->getXpath($location);
                }
                if ($node = $this->mergeNodes($nodeArray)) {
                    $fileName = dirname($file) . ($folder != '' ? (\DIRECTORY_SEPARATOR . $folder) : '')
                        . \DIRECTORY_SEPARATOR . $this->fileName . '.xml';
                    //do additional xml node processing that we can't do in xslt
                    $node = $this->postNodeProcess($node);
                    $handlers[] = $this->configTypeInterfaceFactory->create()
                        ->setFileName($fileName)
                        ->setXmlContent($node)
                        ->setXsls($this->xsls)
                        ->setTagName($this->tagName)
                        ->setXmlSchema($this->xmlSchema)
                        ->setType($this->fileName);
                }
            }
            if (count($handlers) > 0) {
                return $handlers;
            }
        }
        return false;
    }

    /**
     * @param \Magento\Framework\Simplexml\Element $node
     * @return \Magento\Framework\Simplexml\Element|null
     */
    protected function postNodeProcess($node)
    {
        return $node;
    }

    /**
     * @param \Magento\Framework\Simplexml\Element[] $nodeArray
     * @return \Magento\Framework\Simplexml\Element|null
     */
    protected function mergeNodes($nodeArray)
    {
        if (is_array($nodeArray)) {
            if (!empty($nodeArray)) {
                /** @var \Magento\Framework\Simplexml\Element $node */
                $node = current($nodeArray);
                for ($index = 1; $index < count($nodeArray); $index++) {
                    $node->extend($nodeArray[$index]);
                }
                return $node;
            }
        }
        return null;
    }
}
