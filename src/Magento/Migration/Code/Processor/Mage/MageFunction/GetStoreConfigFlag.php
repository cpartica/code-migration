<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class GetStoreConfigFlag extends AbstractFunction implements MageFunctionInterface
{
    /**
     * @var string
     */
    protected $methodName = 'getStoreConfigFlag';

    /**
     * @var int
     */
    protected $endIndex = null;

    /**
     * @var string
     */
    protected $diVariableName = 'scopeConfig';

    /**
     * @var string
     */
    protected $diClass = '\Magento\Framework\App\Config\ScopeConfigInterface';

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return MageFunctionInterface::MAGE_GET_STORE_CONFIG;
    }

    /**
     * @inheritdoc
     */
    public function getClass()
    {
        return $this->diClass;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return $this->methodName;
    }

    /**
     * @inheritdoc
     */
    public function getStartIndex()
    {
        return $this->index;
    }

    /**
     * @inheritdoc
     */
    public function getEndIndex()
    {
        //e.g., Mage::getStoreConfigFlag($path, $store);
        return $this->tokenHelper->skipMethodCall($this->tokens, $this->index) - 1;
    }

    /**
     * @inheritdoc
     */
    public function convertToM2()
    {
        //from Mage::getStoreConfigFlag($path to
        //$this->scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE

        $currentIndex = $this->index;
        $this->tokens[$currentIndex++][1] = ''; //Mage
        $this->tokens[$currentIndex++][1] = ''; //::
        $this->tokens[$currentIndex][1] = '$this->' . $this->diVariableName . '->isSetFlag'; //getStoreConfigFlag
        $arguments = $this->tokenHelper->getCallArguments($this->tokens, $currentIndex);
        $count = $arguments->getCount();

        if ($count > 1) {
            $currentIndex = $this->tokenHelper->getNextIndexOfSimpleToken($this->tokens, $currentIndex, ',');
            $this->tokens[$currentIndex] = ', ' . '\Magento\Store\Model\ScopeInterface::SCOPE_STORE' . ',';
        } else {
            $currentIndex = $this->tokenHelper->getNextIndexOfSimpleToken($this->tokens, $currentIndex, ')');
            $this->tokens[$currentIndex] = ', ' . '\Magento\Store\Model\ScopeInterface::SCOPE_STORE' . ')';
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiVariableName()
    {
        return $this->diVariableName;
    }

    /**
     * @inheritdoc
     */
    public function getDiClass()
    {
        return $this->getClass();
    }
}
