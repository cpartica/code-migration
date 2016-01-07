<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Model;

class ModelMethodMatcher implements \Magento\Migration\Code\Processor\Mage\MatcherInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
    ) {
        $this->objectManager = $objectManager;
        $this->tokenHelper = $tokenHelper;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return \Magento\Migration\Code\Processor\Model\ModelMethodInterface|null
     */
    public function match(&$tokens, $index)
    {
        if (!($this->isVariable($tokens, $index, '$this') && $this->isObjectOperator($tokens, $index + 1))) {
            return null;
        }

        $indexOfMethodCall = $this->tokenHelper->getNextIndexOfTokenType($tokens, $index, T_STRING);
        $methodName = $tokens[$indexOfMethodCall][1];

        switch ($methodName) {
            case '_init':
                /** @var \Magento\Migration\Code\Processor\Model\ModelMethod\Init $result */
                $result = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Model\ModelMethod\Init'
                );
                $result->setContext($tokens, $index);
                return $result;

            default:
                return null;
        }
    }

    /**
     * @param array $tokens
     * @param int $index
     * @param string $variableName
     * @return bool
     */
    protected function isVariable(&$tokens, $index, $variableName)
    {
        return (is_array($tokens[$index]) && $tokens[$index][0] == T_VARIABLE && $tokens[$index][1] == $variableName);
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return bool
     */
    protected function isObjectOperator(&$tokens, $index)
    {
        return (is_array($tokens[$index]) && $tokens[$index][0] == T_OBJECT_OPERATOR);
    }
}
