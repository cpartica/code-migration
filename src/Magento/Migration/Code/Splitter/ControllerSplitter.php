<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Splitter;

use \Magento\Migration\Code\Splitter\ActionHelper;

class ControllerSplitter implements \Magento\Migration\Code\SplitterInterface
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
     * @param \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
     * @param \Magento\Migration\Code\Splitter\Controller\ControllerMethodMatcher $matcher
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Splitter\Controller\ControllerMethodMatcher $matcher
    ) {
        $this->objectManager = $objectManager;
        $this->tokenHelper = $tokenHelper;
        $this->matcher = $matcher;
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
     * @param array $resultFiles
     * @return array
     */
    public function split(array $tokens, &$resultFiles)
    {
        if (!$this->isClass($tokens)) {
            return $tokens;
        }

        if ($this->tokenHelper->isAbstract($tokens)) {
            return $tokens;
        }

        if ($this->tokenHelper->getExtendsClass($tokens) != 'Mage_Core_Controller_Varien_Action' &&
            !preg_match('/[a-zA-Z0-9]{1}Controller/is', $this->tokenHelper->getExtendsClass($tokens))
        ) {
            return $tokens;
        }

        $index = 0;
        $length = count($tokens);

        $actionMethods = [];
        while ($index < $length - 3) {
            $matchedFunction = $this->matcher->match($tokens, $index);
            if ($matchedFunction) {
                $actionMethods[$matchedFunction->getMethodName()] = $matchedFunction->getMethodTokens();
            }
            $index++;
        }

        if (!empty($actionMethods)) {
            /** @var ActionHelper $actionHelper */
            $actionHelper = $this->objectManager->create(
                '\Magento\Migration\Code\Splitter\ActionHelper'
            );
            $actionHelper->setContext($actionMethods)
                ->setAbstractFileName($this->getFilePath())
                ->setAbstractNamespace($this->getM1NameSpace($tokens))
                ->setParentClass($this->tokenHelper->getExtendsClass($tokens))
                ->createActions($resultFiles);
        }

        $this->changeActionFileName($tokens)->convertToAbstract($tokens);//->removeControllerFromClassName($tokens);

        //reconstruct tokens
        $tokens = $this->tokenHelper->refresh($tokens);
        return $tokens;
    }

    /**
     * @param array $tokens
     * @return bool
     */
    protected function isClass(array &$tokens)
    {
        return $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_CLASS) != null;
    }

    /**
     * @param array $tokens
     * @return null|string
     */
    protected function getM1NameSpace(array &$tokens)
    {
        $indexNamespace = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_CLASS);
        if ($indexNamespace == null) {
            return null;
        } else {
            $indexNamespace = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_STRING);
            if ($tokens[$indexNamespace]) {
                return preg_replace(
                    '/Controller$/',
                    '',
                    $tokens[$indexNamespace][1]
                );
            }
            return null;
        }
    }

    /**
     * @param array $tokens
     * @return $this
     */
    protected function changeActionFileName(array &$tokens)
    {
        $indexClass = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_CLASS);
        $classNameIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, $indexClass, T_STRING);
        $fileName = preg_replace('/^.+_([a-zA-Z0-9]+)Controller$/', '$1', $tokens[$classNameIndex][1]);
        $this->filePath = dirname($this->filePath) . \DIRECTORY_SEPARATOR . $fileName . ".php";
        return $this;
    }

    /**
     * @param array $tokens
     * @return $this
     */
    protected function removeControllerFromClassName(array &$tokens)
    {
        $indexClass = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_CLASS);
        $classNameIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, $indexClass, T_STRING);
        $className = preg_replace('/^.+_([a-zA-Z0-9]+)Controller$/', '$1', $tokens[$classNameIndex][1]);
        $tokens[$classNameIndex][1] = $className;
        return $this;
    }

    /**
     * @param array $tokens
     * @return $this
     */
    protected function convertToAbstract(array &$tokens)
    {
        $indexClass = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_CLASS);
        $tokens[$indexClass][1] = 'abstract '. $tokens[$indexClass][1];
        return $this;
    }
}
