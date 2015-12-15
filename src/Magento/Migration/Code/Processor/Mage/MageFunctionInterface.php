<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage;

interface MageFunctionInterface
{
    const MAGE_HELPER = 'helper';
    const MAGE_GET_MODEL = 'getModel';
    const MAGE_GET_RESOURCE_MODEL = 'getResourceModel';
    const MAGE_GET_SINGLETON = 'getSingleton';
    const MAGE_CONSTRUCTOR = 'constructor';
    const MAGE_APP = 'app';
    const MAGE_GET_STORE_CONFIG = 'getStoreConfig';
    const MAGE_DISPATCH_EVENT = 'dispatchEvent';
    const MAGE_REGISTRY = 'registry';
    const MAGE_THROW_EXCEPTION = 'throwException';
    const MAGE_LOG = 'log';
    const MAGE_LOG_EXCEPTION = 'logException';

    /**
     * @param array $tokens
     * @param int $index
     * @return $this
     */
    public function setContext(array &$tokens, $index = 0);

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getClass();

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return int
     */
    public function getStartIndex();

    /**
     * @return int
     */
    public function getEndIndex();

    /**
     * @return $this
     */
    public function convertToM2();

    /**
     * @return string
     */
    public function getDiVariableName();

    /**
     * @return string
     */
    public function getDiClass();
}
