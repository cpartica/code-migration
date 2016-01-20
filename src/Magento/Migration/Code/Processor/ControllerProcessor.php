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

        if (!preg_match('/Controller/', $this->tokenHelper->getNameSpace($tokens))) {
            return $tokens;
        }

        if ($this->tokenHelper->getExtendsClass($tokens) != 'Magento\\Framework\\App\\Action\\Action' &&
        !preg_match('/controller/is', $this->tokenHelper->getExtendsClass($tokens))
        ) {
            return $tokens;
        }

        $this->removeControllerKeywordFromClass($tokens);
        if (preg_match('/Controller\\\.+$/', $this->tokenHelper->getNameSpace($tokens))) {
            $this->changeActionParent($tokens);
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
        return $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_CLASS) !== null;
    }

    /**
     * @param array $tokens
     * @return bool
     */
    protected function isAbstract(array &$tokens)
    {
        return $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_ABSTRACT) !== null;
    }

    /**
     * @param array $tokens
     * @return null|string
     */
    protected function removeControllerKeywordFromClass(array &$tokens)
    {
        $indexClass = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_CLASS);
        if ($indexClass === null) {
            return $this;
        } else {
            $indexClassName = $this->tokenHelper->getNextIndexOfTokenType($tokens, $indexClass, T_STRING);
            if (preg_match('/Controller$/', $tokens[$indexClassName][1])) {
                $tokens[$indexClassName][1] = preg_replace('/Controller$/', '', $tokens[$indexClassName][1]);
            }
            return $this;
        }
    }

    /**
     * @param array $tokens
     * @return null|string
     */
    protected function changeActionParent(array &$tokens)
    {
        $indexExtends = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_EXTENDS);
        if ($indexExtends === null) {
            return $this;
        } else {
            $indexExtends = $this->tokenHelper->getNextIndexOfTokenType($tokens, $indexExtends, T_STRING);
            $indexEndExtends = $this->tokenHelper->getNextIndexOfSimpleToken($tokens, $indexExtends, '{');
            $indexEndExtends = $this->tokenHelper->getPrevIndexOfTokenType($tokens, $indexEndExtends, T_STRING);
            $tokens[$indexExtends][1] = $this->tokenHelper->getNameSpace($tokens);
            for ($index = $indexExtends + 1; $index <= $indexEndExtends; $index++) {
                $tokens[$index] = '';
            }
            return $this;
        }
    }
}
