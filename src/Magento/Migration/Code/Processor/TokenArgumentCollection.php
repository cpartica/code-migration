<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class TokenArgumentCollection
{
    /**
     * @var TokenArgument[]
     */
    protected $tokens = [];

    /**
     * @return TokenArgument[]
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param TokenArgument[] $tokens
     * @return $this
     */
    public function setTokens($tokens)
    {
        $this->tokens = $tokens;
        return $this;
    }

    /**
     * @param TokenArgument $argument
     * @param int $index;
     * @return $this
     */
    public function addToken($argument, $index)
    {
        if (!array_key_exists($index, $this->tokens)) {
            $this->tokens[$index] = $argument;
        }
        return $this;
    }

    /**
     * @param int $index;
     * @return $this
     */
    public function removeToken($index)
    {
        if (array_key_exists($index, $this->tokens)) {
            unset($this->tokens[$index]);
        }
        return $this;
    }

    /**
     * gets the Nth token (N=1 is the first token)
     *
     * @param int $n
     * @return TokenArgument|null
     */
    public function getToken($n)
    {
        $i = 1;
        foreach ($this->getTokens() as $arg) {
            if ($i == $n) {
                return $arg;
            }
            $i++;
        }
        return null;
    }

    /**
     * @return TokenArgument|null
     */
    public function getFirstToken()
    {
        return $this->getToken(1);
    }

    /**
     * gets index of the Nth token (N=1 is the first token)
     *
     * @param int $n
     * @return int|null
     */
    public function getTokenIndex($n)
    {
        $i = 1;
        foreach ($this->getTokens() as $index => $arg) {
            if ($i == $n) {
                return $index;
            }
            $i++;
        }
        return null;
    }

    /**
     * gets all the tokens as a concatenated string
     *
     * @return string
     */
    public function getString()
    {
        $str = '';
        foreach ($this->getTokens() as $arg) {
            $str .=$arg->getName();
        }
        return $str;
    }
}
