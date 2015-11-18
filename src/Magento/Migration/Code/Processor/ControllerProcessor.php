<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class ControllerProcessor implements \Magento\Migration\Code\ProcessorInterface
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
     * @param Controller\ControllerMethodMatcher $matcher
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Processor\Controller\ControllerMethodMatcher $matcher
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
     * @return array
     */
    public function process(array $tokens)
    {
        if (!$this->isClass($tokens)) {
            return $tokens;
        }

        if ($this->isAbstract($tokens)) {
            return $tokens;
        }

        if (!preg_match('/Controller/', $this->getNameSpace($tokens))) {
            return $tokens;
        }

        if ($this->tokenHelper->getExtendsClass($tokens) != 'Magento\\Framework\\App\\Action\\Action' &&
        !preg_match('/controller/is', $this->tokenHelper->getExtendsClass($tokens))
        ) {
            return false;
        }

        $index = 0;
        $length = count($tokens);

        $actionMethods = [];
        while ($index < $length - 3) {
            $matchedFunction = $this->matcher->match($tokens, $index);
            if ($matchedFunction) {
                $matchedFunction->convertToM2();
                $actionMethods[$matchedFunction->getMethodName()] = $matchedFunction->convertToM2()->getMethodTokens();
            }
            $index++;
        }

        $this->abstractizeCurrentClass($tokens);

        if (!empty($actionMethods)) {
            $actionHelper = $this->objectManager->create(
                '\Magento\Migration\Code\Processor\ActionHelper'
            );
             $actionHelper->setContext($actionMethods)
                 ->setAbstractFileName($this->getFilePath())
                 ->setAbstractNamespace($this->getNameSpace($tokens))
                 ->createActions();
        }

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
     * @return bool
     */
    protected function isAbstract(array &$tokens)
    {
        return $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_ABSTRACT) != null;
    }

    /**
     * @param array $tokens
     * @return null|string
     */
    protected function getNameSpace(array &$tokens)
    {
        $indexNamespace = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_NAMESPACE);
        if ($indexNamespace == null) {
            return null;
        } else {
            $indexNamespace = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_STRING);

            $indexEndNamespace = $this->tokenHelper->getNextIndexOfSimpleToken($tokens, $indexNamespace, ';');
            $strNamespace = '';
            for ($index = $indexNamespace; $index <= $indexEndNamespace; $index++) {
                $strNamespace .= $tokens[$index][1];
            }
            return $strNamespace;
        }
    }

    /**
     * @param array $tokens
     * @return $this
     */
    protected function abstractizeCurrentClass($tokens) {
        //convert the non action class into abstract
        $this->convertToAbstract($tokens)
            ->changeClassName($tokens)
            ->changeFileName($tokens);
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

    /**
     * @param array $tokens
     * @return $this
     */
    protected function changeClassName(array &$tokens)
    {
        $indexClass = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_CLASS);
        $classNameIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, $indexClass, T_STRING);
        $tokens[$classNameIndex][1] = ucfirst(
            preg_replace(
                '/Controller$/',
                '',
                $tokens[$classNameIndex][1]
            )
        );
        return $this;
    }

    /**
     * @param array $tokens
     * @return $this
     */
    protected function changeFileName(array &$tokens)
    {
        $indexClass = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_CLASS);
        $classNameIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, $indexClass, T_STRING);
        $this->filePath = dirname($this->filePath) . \DIRECTORY_SEPARATOR . $tokens[$classNameIndex][1] . ".php";
        return $this;
    }
}
