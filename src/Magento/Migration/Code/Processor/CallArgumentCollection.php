<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class CallArgumentCollection
{
    /**
     * @var TokenArgumentCollection[]
     */
    protected $arguments = [];

    /**
     * @return TokenArgumentCollection[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param TokenArgumentCollection[] $arguments
     * @return $this
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @param TokenArgumentCollection $argument
     * @param int $index;
     * @return $this
     */
    public function addArgument($argument, $index = null)
    {
        if ($index === null) {
            $this->arguments[] = $argument;
        } elseif (!array_key_exists($index, $this->arguments)) {
            $this->arguments[$index] = $argument;
        }
        return $this;
    }

    /**
     * @param int $index;
     * @return $this
     */
    public function removeArgument($index)
    {
        if (array_key_exists($index, $this->arguments)) {
            unset($this->arguments[$index]);
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this->arguments);
    }

    /**
     * gets the Nth argument (N=1 is the first token)
     *
     * @param int $index
     * @return TokenArgumentCollection|null
     */
    public function getArgument($index)
    {
        $cnt = 1;
        foreach ($this->getArguments() as $arg) {
            if ($cnt == $index) {
                return $arg;
            }
            $cnt++;
        }
        return null;
    }

    /**
     * @return TokenArgumentCollection|null
     */
    public function getFirstArgument()
    {
        return $this->getArgument(1);
    }

    /**
     * gets the index of the Nth argument  (N=1 is the first token)
     * @param int $index
     * @return int|null
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getArgumentIndex($index)
    {
        $cnt = 1;
        foreach ($this->getArguments() as $arrIndex => $arg) {
            if ($cnt == $index) {
                return $arrIndex;
            }
            $cnt++;
        }
        return null;
    }
}
