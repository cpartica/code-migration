<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class TableProcessor implements \Magento\Migration\Code\ProcessorInterface
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
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Table\TableFunctionMatcher $matcher
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Migration\Code\Processor\Table\TableFunctionMatcher $matcher
    ) {
        $this->objectManager = $objectManager;
        $this->matcher = $matcher;
    }

    /**
     * @param array $tokens
     * @return array
     */
    public function process(array $tokens)
    {
        $index = 0;
        $length = count($tokens);

        while ($index < $length - 3) {
            $matchedFunction = $this->matcher->match($tokens, $index);
            if ($matchedFunction) {
                $matchedFunction->convertToM2();
            }
            $index++;
        }
        return $tokens;
    }
}
