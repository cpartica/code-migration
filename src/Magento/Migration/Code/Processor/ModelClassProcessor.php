<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class ModelClassProcessor implements \Magento\Migration\Code\ProcessorInterface
{
    /**#@+
     * Patterns that match model class names
     */
    const MODEL_CLASS_UNDERSCORE    = '/^[^_]+_[^_]+_Model_(?!Resource_|Mysql4_)/';
    const MODEL_CLASS_NAMESPACE     = '/^\\\\?[^\\\\]+\\\\[^\\\\]+\\\\Model\\\\(?!ResourceModel\\\\)/';
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
     * @param Model\ModelMethodMatcher $matcher
     * @param TokenHelper $tokenHelper
     */
    public function __construct(
        \Magento\Migration\Code\Processor\Model\ModelMethodMatcher $matcher,
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
        if (!$this->isModelClass($tokens)) {
            return $tokens;
        }

        for ($index = 0; $index < count($tokens) - 3; $index++) {
            $matchedMethod = $this->matcher->match($tokens, $index);
            if ($matchedMethod) {
                $matchedMethod->convertToM2();
            }
        }
        $tokens = $this->tokenHelper->refresh($tokens);
        return $tokens;
    }

    /**
     * @param array $tokens
     * @return bool
     */
    protected function isModelClass(array &$tokens)
    {
        $parentClass = $this->tokenHelper->getExtendsClass($tokens);
        if ($parentClass) {
            return preg_match(self::MODEL_CLASS_UNDERSCORE, $parentClass)
                || preg_match(self::MODEL_CLASS_NAMESPACE, $parentClass);
        }
        return false;
    }
}
