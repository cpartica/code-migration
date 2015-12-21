<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class BlockClassProcessor implements \Magento\Migration\Code\ProcessorInterface
{
    /**#@+
     * Patterns that match block class names
     */
    const BLOCK_CLASS_UNDERSCORE    = '/^.+?_.+?_Block_/';
    const BLOCK_CLASS_NAMESPACE     = '/^\\\\?.+?\\\\.+?\\\\Block\\\\/';
    /**#@-*/

    /**
     * @var string $filePath
     */
    protected $filePath;

    /**
     * @var \Magento\Migration\Code\Processor\Mage\MatcherInterface
     */
    protected $matcher;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @param Block\BlockFunctionMatcher $matcher
     * @param TokenHelper $tokenHelper
     */
    public function __construct(
        \Magento\Migration\Code\Processor\Block\BlockFunctionMatcher $matcher,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
    ) {
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
        if (!$this->isBlockClass($tokens)) {
            return $tokens;
        }
        for ($index = 0; $index < count($tokens) - 3; $index++) {
            $matchedFunction = $this->matcher->match($tokens, $index);
            if ($matchedFunction) {
                $matchedFunction->convertToM2();
            }
        }
        $tokens = $this->tokenHelper->refresh($tokens);
        return $tokens;
    }

    /**
     * @param array $tokens
     * @return bool
     */
    protected function isBlockClass(array &$tokens)
    {
        $parentClass = $this->tokenHelper->getExtendsClass($tokens);
        if ($parentClass) {
            return preg_match(self::BLOCK_CLASS_UNDERSCORE, $parentClass)
                || preg_match(self::BLOCK_CLASS_NAMESPACE, $parentClass);
        }
        return false;
    }
}
