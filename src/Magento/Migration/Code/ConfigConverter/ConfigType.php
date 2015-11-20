<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter;

use \Magento\Migration\Code\ConfigConverter\ConfigTypeInterface;

class ConfigType implements ConfigTypeInterface
{
    /**
     * @var \Magento\Framework\Simplexml\Element $xmlConfig
     */
    protected $xmlElement;

    /**
     * @var string $filename
     */
    protected $filename;

    /**
     * @var string $type
     */
    protected $type;

    /**
     * @param \Magento\Framework\Simplexml\Element $element
     * @return $this
     */
    public function setXmlContent($element)
    {
        $this->xmlElement = $element;
        return $this;
    }

    /**
     * @return \Magento\Framework\Simplexml\Element
     */
    public function getXmlContent()
    {
        return $this->xmlElement;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFileName($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->filename;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
