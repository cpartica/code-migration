<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

/**
 * Class AbstractFunction
 * Base class for
 * @package Magento\Migration\Code\Processor\Mage\MageFunction
 */
class AbstractFunction
{
    /**
     * @var \Magento\Migration\Mapping\ClassMapping
     */
    protected $classMapper;
    /**
     * @var \Magento\Migration\Mapping\Alias
     */
    protected $aliasMapper;

    /**
     * @var array
     */
    protected $tokens;

    /**
     * @var int
     */
    protected $index;

    /**
     * @var \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory
     */
    protected $argumentFactory;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @param \Magento\Migration\Mapping\ClassMapping $classMapper
     * @param \Magento\Migration\Mapping\Alias $aliasMapper
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
     * @param ArgumentFactory $argumentFactory
     */
    public function __construct(
        \Magento\Migration\Mapping\ClassMapping $classMapper,
        \Magento\Migration\Mapping\Alias $aliasMapper,
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory $argumentFactory
    ) {
        $this->classMapper = $classMapper;
        $this->aliasMapper = $aliasMapper;
        $this->logger = $logger;
        $this->tokenHelper = $tokenHelper;
        $this->argumentFactory = $argumentFactory;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return void
     */
    public function setContext(array &$tokens, $index = 0)
    {
        $this->tokens = &$tokens;
        $this->index = $index;
    }

    /**
     * Return the token of first argument of a Mage static call
     *
     * @param int $index
     * @return array|string
     */
    protected function getMageCallFirstArgument($index)
    {
        //Mage::helper('core') or Mage::getModel('core', 'additionalArguments')
        $index = $this->tokenHelper->getNextTokenIndex($this->tokens, $index, 3);
        return $this->tokens[$index];
    }

    /**
     * @param int $index
     * @param string $methodName
     * @return bool
     */
    protected function isMageGetGenericCall($index, $methodName)
    {
        if (!$this->isMageCall($index)) {
            return false;
        }

        if (!isset($this->tokens[$index + 2])) {
            return false;
        }
        return is_array($this->tokens[$index + 2])
        && $this->tokens[$index + 2][0] == T_STRING
        && $this->tokens[$index + 2][1] == $methodName;
    }

    /**
     * @param int $index
     * @return bool
     */
    protected function isMageCall($index)
    {
        if (!is_array($this->tokens[$index])
            || $this->tokens[$index][0] != T_STRING
            || $this->tokens[$index][1] != 'Mage') {
            return false;
        }

        if (!isset($this->tokens[$index + 1])) {
            return false;
        }

        return is_array($this->tokens[$index + 1]) && $this->tokens[$index + 1][0] == T_DOUBLE_COLON;
    }
}
