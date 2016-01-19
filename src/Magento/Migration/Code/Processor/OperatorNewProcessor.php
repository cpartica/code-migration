<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class OperatorNewProcessor implements \Magento\Migration\Code\ProcessorInterface
{
    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @var \Magento\Migration\Code\Processor\ConstructorHelper
     */
    protected $constructorHelper;

    /**
     * @var \Magento\Migration\Code\Processor\NamingHelper
     */
    protected $namingHelper;

    /**
     * @var string $filePath
     */
    protected $filePath;

    /**
     * @param TokenHelper $tokenHelper
     * @param ConstructorHelper $constructorHelper
     * @param NamingHelper $namingHelper
     */
    public function __construct(
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Processor\ConstructorHelper $constructorHelper,
        \Magento\Migration\Code\Processor\NamingHelper $namingHelper
    ) {
        $this->tokenHelper = $tokenHelper;
        $this->constructorHelper = $constructorHelper;
        $this->namingHelper = $namingHelper;
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
        $isWithinClass = $this->isClass($tokens);

        $diVariables = [];
        foreach ($this->findOperatorNewTokens($tokens) as $tokenInfo) {
            $indexFrom = $tokenInfo['index_from'];
            $indexTo = $tokenInfo['index_to'];
            $indexClass = $tokenInfo['index_class'];
            $m1ClassName = $tokenInfo['class_name'];
            $isPrecededByThrow = $this->isPrecededByThrow($tokens, $indexFrom);
            if ($isWithinClass && !$isPrecededByThrow && $this->isFactoryNeeded($m1ClassName)) {
                $m2ClassName = $this->namingHelper->getM2FactoryClassName($m1ClassName);
                $variableName = $this->namingHelper->generateVariableName($m2ClassName);
                $diVariables[$variableName] = [
                    'variable_name' => $variableName,
                    'type' => $m2ClassName,
                ];
                $this->tokenHelper->eraseTokens($tokens, $indexFrom, $indexTo);
                $replacement = '$this->' . $variableName . '->create';
                if (!$this->isFollowedByArguments($tokens, $indexFrom)) {
                    $replacement .= '()';
                }
                //TODO: handle constructor arguments
            } else {
                $m2ClassName = $this->namingHelper->getM2ClassName($m1ClassName);
                $replacement = $m2ClassName;
            }
            if ($replacement) {
                $tokens[$indexClass][1] = $replacement;
            }
        }

        if ($diVariables) {
            $this->constructorHelper->setContext($tokens);
            $this->constructorHelper->injectArguments($diVariables);
        }

        $tokens = $this->tokenHelper->refresh($tokens);
        return $tokens;
    }

    /**
     * @param array $tokens
     * @return array
     */
    protected function findOperatorNewTokens(array &$tokens)
    {
        $result = [];
        $currentIndex = -1;
        while ($currentIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, $currentIndex + 1, T_NEW)) {
            $nextTokenIndex = $this->tokenHelper->getNextTokenIndex($tokens, $currentIndex);
            if ($nextTokenIndex === null) {
                break;
            }
            $nextToken = $tokens[$nextTokenIndex];
            if (is_array($nextToken) && $nextToken[0] == T_STRING) {
                $m1ClassName = $nextToken[1];
                $result[] = [
                    'index_from' => $currentIndex,
                    'index_to' => $nextTokenIndex,
                    'index_class' => $nextTokenIndex,
                    'class_name' => $m1ClassName,
                ];
            }
        }
        return $result;
    }

    /**
     * Whether a given token is preceded by a throw token on the same line
     *
     * @param array $tokens
     * @param int $currentIndex
     * @return bool
     */
    protected function isPrecededByThrow(array $tokens, $currentIndex)
    {
        $throwTokenIndex = $this->tokenHelper->getPrevIndexOfTokenType($tokens, $currentIndex, T_THROW);
        if ($throwTokenIndex !== null) {
            $currentLine = $tokens[$currentIndex][2];
            $throwTokenLine = $tokens[$throwTokenIndex][2];
            return ($throwTokenLine == $currentLine);
        }
        return false;
    }

    /**
     * Whether a given token is followed by argument tokens on the same line
     *
     * @param array $tokens
     * @param int $currentIndex
     * @return bool
     */
    protected function isFollowedByArguments(array $tokens, $currentIndex)
    {
        try {
            $argumentsIndex = $this->tokenHelper->getNextIndexOfSimpleToken($tokens, $currentIndex, '(');
            $currentLine = $tokens[$currentIndex][2];
            $nextLineIndex = $this->tokenHelper->getNextLineIndex($tokens, $currentIndex, $currentLine);
            return ($nextLineIndex === null || $nextLineIndex > $argumentsIndex);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Whether creation of a class instance is to be delegated to a corresponding factory
     *
     * @param string $className
     * @return bool
     */
    protected function isFactoryNeeded($className)
    {
        $className = ltrim($className, '\\');
        if (strpos($className, 'Exception') !== false) {
            return false;
        }
        if (strpos($className, 'Zend_') === 0) {
            return false;
        }
        if (strpos($className, '_') === false && strpos($className, '\\') === false) {
            return false;
        }
        return true;
    }

    /**
     * @param array $tokens
     * @return bool
     */
    protected function isClass(array &$tokens)
    {
        return $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_CLASS) !== null;
    }
}
