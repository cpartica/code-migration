<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class TableProcessor implements \Magento\Migration\Code\ProcessorInterface
{
    /**
     * @var string $filePath
     */
    protected $filePath;

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
     * @param Table\TableFunctionMatcher $matcher
     * @param TokenHelper $tokenHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Migration\Code\Processor\Table\TableFunctionMatcher $matcher,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
    ) {
        $this->objectManager = $objectManager;
        $this->matcher = $matcher;
        $this->tokenHelper = $tokenHelper;
    }

    /**
     * @param string $filePath
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
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
        $tokens = $this->tokenHelper->refresh($tokens);
        return $tokens;
    }
}
