<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter;

class ConfigType implements ConfigTypeInterface
{
    /**
     * @var \Magento\Framework\Simplexml\Element
     */
    protected $xmlElement;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string[]
     */
    protected $xsls = [];

    /**
     * @var string
     */
    protected $xmlSchema;

    /**
     * @var string
     */
    protected $tagName;

    /**
     * @var string
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

    /**
     * @param string[] $xsls
     * @return $this
     */
    public function setXsls($xsls)
    {
        $this->xsls = $xsls;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getXsls()
    {
        return $this->xsls;
    }

    /**
     * @param string $xmlSchema
     * @return $this
     */
    public function setXmlSchema($xmlSchema)
    {
        $this->xmlSchema = $xmlSchema;
        return $this;
    }

    /**
     * @return string
     */
    public function getXmlSchema()
    {
        return $this->xmlSchema;
    }

    /**
     * @param string $tagName
     * @return $this
     */
    public function setTagName($tagName)
    {
        $this->tagName = $tagName;
        return $this;
    }

    /**
     * @return string
     */
    public function getTagName()
    {
        return $this->tagName;
    }
}
