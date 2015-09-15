<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class MageProcessor implements \Magento\Migration\Code\ProcessorInterface
{
    /**
     * @var \Magento\Migration\Code\Processor\Mage\MatcherInterface
     */
    protected $matcher;

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
     * @param Mage\MageFunctionMatcher $matcher
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Processor\Mage\MageFunctionMatcher $matcher
    ) {
        $this->objectManager = $objectManager;
        $this->tokenHelper = $tokenHelper;
        $this->matcher = $matcher;
    }

    /**
     * @param array $tokens
     * @return array
     */
    public function process(array $tokens)
    {
        if (!$this->isClass($tokens)) {
            return $tokens;
        }

        $index = 0;
        $length = count($tokens);

        $diVariables = [];
        while ($index < $length - 3) {
            $matchedFunction = $this->matcher->match($tokens, $index);
            if ($matchedFunction) {
                $matchedFunction->convertToM2();
                if ($matchedFunction->getDiVariableName() != null) {
                    $diVariables[$matchedFunction->getDiVariableName()] = [
                        'variable_name' => $matchedFunction->getDiVariableName(),
                        'type' => $matchedFunction->getClass(),
                    ];
                }
            }
            $index++;
        }

        //TODO: avoid adding di variable if parent class already has variable of the same type
        if (!empty($diVariables)) {
            /** @var \Magento\Migration\Code\Processor\Mage\MageFunction\Constructor $constructorHelper */
            $constructorHelper = $this->objectManager->create(
                '\Magento\Migration\Code\Processor\Mage\MageFunction\Constructor'
            );
            $constructorHelper->setContext($tokens, $index);
            $constructorHelper->injectArguments($diVariables);
        }
        return $tokens;
    }

    protected function isClass(array &$tokens)
    {
        return $this->tokenHelper->getNextIndexOfType($tokens, 0, T_CLASS) != null;
    }
}
