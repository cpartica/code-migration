<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter;

interface ConfigTypeInterface
{
    /**
     * @param \Magento\Framework\Simplexml\Element $element
     * @return $this
     */
    public function setXmlContent($element);

    /**
     * @return \Magento\Framework\Simplexml\Element
     */
    public function getXmlContent();

    /**
     * @param string $filename
     * @return $this
     */
    public function setFileName($filename);

    /**
     * @return string
     */
    public function getFileName();

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getType();
}
