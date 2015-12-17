<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Splitter\Controller\ControllerMethod;

use Magento\Migration\Code\Splitter\Controller\ControllerMethodInterface;

class Action extends AbstractFunction implements ControllerMethodInterface
{
    /**
     * @var array
     */
    protected $methodTokens = [];

    /**
     * @var string
     */
    protected $methodName = null;

    /**
     * @var int
     */
    protected $endIndex = null;

    /**
     * @var bool
     */
    protected $parsed = false;

    /**
     * @return $this
     */
    private function parse()
    {
        $this->parsed = true;
        $blockStartIndex = $this->tokenHelper->getFunctionStartingIndex($this->tokens, $this->index);
        $this->endIndex = $this->tokenHelper->skipBlock($this->tokens, $blockStartIndex);

        $this->index = $this->tokenHelper->getPrevIndexOfSimpleToken($this->tokens, $this->index, ['}', '{', ';']) + 1;

        $this->methodName = ucfirst(
            preg_replace(
                '/Action$/',
                '',
                $this->tokens[$this->tokenHelper->getNextIndexOfTokenType($this->tokens, $this->index, T_STRING)][1]
            )
        );
        return $this;
    }


    /**
     * Return the token of argument of a getMethodName call
     *
     * @return array|string|void
     */
    public function getMethodName()
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return $this->methodName;
    }

    /**
     * @inheritdoc
     */
    public function getStartIndex()
    {
        return $this->index;
    }

    /**
     * @inheritdoc
     */
    public function getEndIndex()
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return $this->endIndex;
    }

    /**
     * @return array
     */
    public function getMethodTokens()
    {
        if (!$this->parsed) {
            $this->parse();
        }
        if (empty($this->methodTokens)) {
            $this->convertToM2();
        }
        return $this->methodTokens;
    }

    /**
     * @inheritdoc
     */
    public function convertToM2()
    {
        if (!$this->parsed) {
            $this->parse();
        }
        if ($this->methodName == null) {
            return $this;
        }

        for ($index = $this->getStartIndex(); $index <= $this->getEndIndex(); $index++) {
            $this->methodTokens[] = $this->tokens[$index];
            $this->tokens[$index] = $index == $this->getStartIndex() ? "\n" : '';
        }

        return $this;
    }
}
