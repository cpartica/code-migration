<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Splitter\Controller;

class ControllerMethodMatcher implements \Magento\Migration\Code\Processor\Mage\MatcherInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->tokenHelper = $tokenHelper;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return ControllerMethodInterface|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function match(&$tokens, $index)
    {
        if (!$this->isMethod($tokens, $index)) {
            return null;
        }

        $methodName =  $tokens[$this->tokenHelper->getNextTokenIndex($tokens, $index)][1];

        if (preg_match('/Action$/', $methodName)) {
            $helperFunction = $this->objectManager->create(
                '\Magento\Migration\Code\Splitter\Controller\ControllerMethod\Action'
            );
            $helperFunction->setContext($tokens, $index);
            return $helperFunction;
        }
        return null;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return bool
     */
    protected function isMethod(&$tokens, $index)
    {
        if (!is_array($tokens[$index]) || $tokens[$index][0] != T_FUNCTION) {
            return false;
        }
        return  true;
    }
}
