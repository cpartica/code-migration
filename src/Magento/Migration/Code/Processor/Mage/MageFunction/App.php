<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class App extends AbstractFunction implements \Magento\Migration\Code\Processor\Mage\MageFunctionInterface
{
    /**
     * @var string
     */
    protected $methodName = null;

    /**
     * @var int
     */
    protected $endIndex = null;

    /**
     * @var string
     */
    protected $diVariableName = null;

    /**
     * @var string
     */
    protected $diClass = null;

    /**
     * @var bool
     */
    protected $parsed = false;

    /**
     * Some method are wrappers to get singleton object
     * For example: Mage::app()->getLayout()-> becomes $this->layout->
     *
     * @var array
     */
    protected $methodToM1ClassMap = [
        'getLocale' => 'Mage_Core_Model_Locale',
        'getLayout' => 'Mage_Core_Model_Layout',
        'getTranslator' => 'Mage_Core_Model_Translate',
        'getConfig' => 'Mage_Core_Model_Config',
        'getRequest' => 'Mage_Core_Controller_Request_Http',
    ];

    /**
     * Mapping for methods that have been moved from Mage_Core_Model_App to a different class
     * For example: Mage::app()->getStore( will become $this->storeManager->getStore(
     *
     * @var array
     */
    protected $methodToM2Map = [
        'getStore' => [
            'class' => '\Magento\Store\Model\StoreManagerInterface',
            'variable_name' => 'storeManager',
        ],
        'getStores' => [
            'class' => '\Magento\Store\Model\StoreManagerInterface',
            'variable_name' => 'storeManager',
        ],
        'getWebsite' => [
            'class' => '\Magento\Store\Model\StoreManagerInterface',
            'variable_name' => 'storeManager',
        ],
        'getWebsites' => [
            'class' => '\Magento\Store\Model\StoreManagerInterface',
            'variable_name' => 'storeManager',
        ],
        'getGroup' => [
            'class' => '\Magento\Store\Model\StoreManagerInterface',
            'variable_name' => 'storeManager',
        ],
        'getGroups' => [
            'class' => '\Magento\Store\Model\StoreManagerInterface',
            'variable_name' => 'storeManager',
        ],
        'getCookie' => [
            'class' => '\Magento\Framework\Stdlib\CookieManagerInterface',
            'variable_name' => 'cookieManager',
        ]
    ];

    /**
     * Functions related to caching
     *
     * @var array
     */
    protected $cacheFunctions = [
        'saveCache' => 'save',
        'loadCache' => 'load',
        'removeCache' => 'remove',
        'cleanCache' => 'clean',
    ];

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function parse()
    {
        $this->parsed = true;

        $endOfMethodCallIndex = $this->tokenHelper->skipMethodCall($this->tokens, $this->index) - 1;
        $nextTokenIndex = $this->tokenHelper->getNextTokenIndex($this->tokens, $endOfMethodCallIndex);
        $nextNextTokenIndex = $this->tokenHelper->getNextTokenIndex($this->tokens, $nextTokenIndex);
        if (!is_array($this->tokens[$nextTokenIndex]) || $this->tokens[$nextTokenIndex][0] != T_OBJECT_OPERATOR
            || !is_array($this->tokens[$nextNextTokenIndex]) || $this->tokens[$nextNextTokenIndex][0] != T_STRING) {
            $this->logger->warn('Method call not found after Mage::app() call');
            return $this;
        }
        //e.g., Mage:app()->getCookie()->doSomething(
        $this->methodName = $this->tokens[$nextNextTokenIndex][1]; //getCookie

        if (isset($this->methodToM1ClassMap[$this->methodName])) {
            $m1Class = $this->methodToM1ClassMap[$this->methodName];
            $m2Class = $this->classMapper->mapM1Class($m1Class);
            if ($m2Class && $m2Class != 'obsolete') {
                //e.g. Mage::app()->getRequest()->
                $this->diVariableName = $this->generateVariableName($this->methodName);
                $this->diClass = $m2Class;
                $this->endIndex = $this->tokenHelper->skipMethodCall($this->tokens, $nextNextTokenIndex) - 1;
            } elseif ($m2Class == 'obsolete') {
                //e.g., Mage:app()->getLayout()->doSomething(
                //TODO: handle chained method calls if possible
                return $this;
            } else {
                $this->logger->warn('Method in Mage_Core_Model_App not converted: ' . $this->methodName);
                return $this;
            }
        } elseif (isset($this->methodToM2Map[$this->methodName])) {
            //e.g., Mage::app()->getStore(...
            $this->diClass = $this->methodToM2Map[$this->methodName]['class'];
            $this->diVariableName = $this->methodToM2Map[$this->methodName]['variable_name'];
            $this->endIndex = $endOfMethodCallIndex;
        } elseif (array_key_exists($this->methodName, $this->cacheFunctions)) {
            //Mage::app()->loadCache
            $this->diClass = '\Magento\Framework\Cache\FrontendInterface';
            $this->diVariableName = 'cache';
            $this->endIndex = $nextNextTokenIndex;
        } else {
            $this->logger->warn('Method in Mage_Core_Model_App not converted: ' . $this->methodName);
        }

        return $this;
    }

    /**
     * @param string $methodName
     * @return string
     */
    protected function generateVariableName($methodName)
    {
        //skip 'get'
        $variableName = substr($methodName, 3);
        $variableName = lcfirst($variableName);
        return $variableName;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return MageFunctionInterface::MAGE_APP;
    }

    /**
     * @inheritdoc
     */
    public function getClass()
    {
        if (!$this->parsed) {
            $this->parse();
        }

        return $this->diClass;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        if (!$this->parsed) {
            $this->parse();
        }
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
        if (!$this->parsed) {
            $this->parse();
        }
        return $this->endIndex;
    }

    /**
     * @inheritdoc
     */
    public function convertToM2()
    {
        if (!$this->parsed) {
            $this->parse();
        }

        if ($this->methodName === null || $this->diClass === null || $this->diVariableName === null) {
            return $this;
        }

        $this->tokenHelper->eraseTokens($this->tokens, $this->index, $this->endIndex);

        $this->tokens[$this->index] = '$this->' . $this->diVariableName;

        if (array_key_exists($this->methodName, $this->cacheFunctions)) {
            $this->tokens[$this->index] .= '->' . $this->cacheFunctions[$this->methodName];
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiVariableName()
    {
        if (!$this->parsed) {
            $this->parse();
        }

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
