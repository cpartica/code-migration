<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class TokenHelper
{
    /**
     * @var \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory
     */
    protected $argumentFactory;

    /**
     * @var \Magento\Migration\Code\Processor\CallArgumentCollectionFactory
     */
    protected $callCollectionFactory;

    /**
     * @var \Magento\Migration\Code\Processor\TokenArgumentCollectionFactory
     */
    protected $tokenCollectionFactory;

    /**
     * @var \Magento\Migration\Code\Processor\TokenArgumentFactory
     */
    protected $tokenFactory;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Migration\Logger\Logger $logger
     * @param Mage\MageFunction\ArgumentFactory $argumentFactory
     * @param \Magento\Migration\Code\Processor\TokenArgumentFactory $tokenFactory
     * @param \Magento\Migration\Code\Processor\TokenArgumentCollectionFactory $tokenCollectionFactory
     * @param \Magento\Migration\Code\Processor\CallArgumentCollectionFactory $callCollectionFactory
     */
    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory $argumentFactory,
        \Magento\Migration\Code\Processor\TokenArgumentFactory $tokenFactory,
        \Magento\Migration\Code\Processor\TokenArgumentCollectionFactory $tokenCollectionFactory,
        \Magento\Migration\Code\Processor\CallArgumentCollectionFactory $callCollectionFactory
    ) {
        $this->logger = $logger;
        $this->argumentFactory = $argumentFactory;
        $this->tokenFactory = $tokenFactory;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
        $this->callCollectionFactory = $callCollectionFactory;
    }

    /**
     * Get index of token after skipping white space and numSkips non-whitespace tokens
     *
     * @param array $tokens
     * @param int $index
     * @param int $numSkips
     * @return null|int
     */
    public function getNextTokenIndex(array &$tokens, $index, $numSkips = 0)
    {
        $increment = 1;
        $skipped = 0;
        while (isset($tokens[$index + $increment])) {
            if (is_array($tokens[$index + $increment]) && $tokens[$index + $increment][0] == T_WHITESPACE) {
                $increment++;
                continue;
            } else {
                if ($skipped == $numSkips) {
                    return $index + $increment;
                }
                $skipped++;
                $increment++;
            }
        }
        return null;
    }

    /**
     * Return the index of a function call
     *
     * @param array $tokens
     * @param int $index
     * @return int|null
     * @throws \Exception
     */
    public function skipFunctionCall(array &$tokens, $index)
    {
        $count = count($tokens);
        //find the first (, then find match )
        $nextIndex = $this->getNextIndexOf($tokens, $index, '(') + 1;

        $nestedLevel = 0; //used to skip nested (), can't handle quote though
        $found = false;
        while (!$found && $nextIndex < $count) {
            if (is_array($tokens[$nextIndex])) {
                $nextIndex++;
                continue;
            }
            if ($tokens[$nextIndex] == '(') {
                $nestedLevel++;
                $nextIndex++;
            } elseif ($tokens[$nextIndex] == ')') {
                if ($nestedLevel == 0) {
                    break;
                } else {
                    $nestedLevel--;
                    $nextIndex++;
                }
            } else {
                $nextIndex++;
            }
        }

        if ($nextIndex == $count) {
            throw new \Exception("Unexpected token structure");
        }
        $nextIndex++;
        return $nextIndex;
    }

    /**
     * Return the index after function definition
     *
     * @param array $tokens
     * @param int $index
     * @return int
     */
    public function skipFunctionDefinition(array &$tokens, $index)
    {
        //find the first {, then find match }
        $nextIndex = $this->getNextIndexOf($tokens, $index, '{') + 1;

        $nestedLevel = 0; //used to skip nested (), can't handle quote though
        $found = false;
        while (!$found) {
            if (is_array($tokens[$nextIndex])) {
                $nextIndex++;
                continue;
            }
            if ($tokens[$nextIndex] == '{') {
                $nestedLevel++;
            }
            if ($tokens[$nextIndex] == '}') {
                if ($nestedLevel == 0) {
                    break;
                } else {
                    $nestedLevel--;
                }
            }
            $nextIndex++;
        }

        return $nextIndex + 1;
    }

    /**
     * Get the index of token that equals input token
     *
     * @param array $tokens
     * @param int $index
     * @param string $token
     * @return int
     * @throws \Exception
     */
    public function getNextIndexOf(array &$tokens, $index, $token)
    {
        $count = count($tokens);
        while (is_array($tokens[$index]) || $tokens[$index] != $token) {
            $index++;
            if ($index == $count) {
                throw new \Exception('Token' . $token . ' not found after index ' . $index);
            }
        }

        return $index;
    }

    /**
     * Return the index of next occurance of token of type $tokenType and matches $tokenValue if provided
     *
     * @param array $tokens
     * @param int $index
     * @param int $tokenType
     * @param string $tokenValue
     * @return null|int
     */
    public function getNextIndexOfType(array &$tokens, $index, $tokenType, $tokenValue = null)
    {
        $length = count($tokens);
        while ($index < $length) {
            if (!is_array($tokens[$index]) || $tokens[$index][0] != $tokenType) {
                $index++;
                continue;
            }
            if ($tokenValue !== null && $tokens[$index][1] != $tokenValue) {
                $index++;
                continue;
            }
            return $index;
        }
        return null;
    }

    /**
     * Return index of the previous occurance of token of type $tokenType and matches $tokenValue if provided
     *
     * @param array $tokens
     * @param int $index
     * @param int $tokenType
     * @param string $tokenValue
     * @return null|int
     */
    public function getPrevIndexOfType(array &$tokens, $index, $tokenType, $tokenValue = null)
    {
        while ($index > 0) {
            if (!is_array($tokens[$index]) || $tokens[$index][0] != $tokenType) {
                $index--;
                continue;
            }
            if ($tokenValue !== null && $tokens[$index][1] != $tokenValue) {
                $index--;
                continue;
            }
            return $index;
        }
        return null;
    }

    /**
     * Get the argument of a function
     *
     * @param array $tokens
     * @param $startingIndex
     * @return \Magento\Migration\Code\Processor\Mage\MageFunction\Argument[]
     */
    public function getFunctionArguments(array &$tokens, $startingIndex)
    {
        $arguments = [];
        $startingIndex = $this->getNextIndexOf($tokens, $startingIndex, '(');
        $endingIndex = $this->getNextIndexOf($tokens, $startingIndex, ')');

        $index = $startingIndex;
        while ($index < $endingIndex) {
            if (!is_array($tokens[$index]) || $tokens[$index][0] == T_WHITESPACE) {
                $index++;
                continue;
            }
            $argument = [];
            if (($tokens[$index][0] == T_STRING || $tokens[$index][0] == T_ARRAY)
                && (is_array($tokens[$index + 2]) && $tokens[$index + 2][0] == T_VARIABLE)
            ) {
                $variableIndex = $this->getNextIndexOfType($tokens, $index, T_VARIABLE);
                /** @var \Magento\Migration\Code\Processor\Mage\MageFunction\Argument $argument */
                $argument = $this->argumentFactory->create()
                    ->setType($tokens[$index][1])
                    ->setName($tokens[$variableIndex][1]);
                if (!is_array($tokens[$variableIndex + 2]) && $tokens[$variableIndex + 2] == '=') {
                    $argument->setIsOptional(true);
                }
                $index = $variableIndex + 1;
            } elseif ($tokens[$index][0] == T_VARIABLE) {
                /** @var \Magento\Migration\Code\Processor\Mage\MageFunction\Argument $argument */
                $argument = $this->argumentFactory->create()
                    ->setName($tokens[$index][1]);
                if (!is_array($tokens[$index + 2]) && $tokens[$index + 2] == '=') {
                    $argument->setIsOptional(true);
                }
                $index++;
            } else {
                $index++;
            }
            if (!empty($argument)) {
                $arguments[] = $argument;
            }
        }
        return $arguments;
    }

    public function getFunctionStartingIndex(array &$tokens, $index)
    {
        //first, go to previous line
        $lineNumber = $tokens[$index][2];

        $currentIndex = $index - 1;
        while (is_array($tokens[$currentIndex]) && $tokens[$currentIndex][2] == $lineNumber) {
            $currentIndex--;
        }

        if (is_array($tokens[$currentIndex - 1]) && $tokens[$currentIndex - 1][0] == T_DOC_COMMENT) {
            return $currentIndex - 2;
        } else {
            return $currentIndex;
        }
    }

    /**
     * Return the index of first token on the next line
     *
     * @param array $tokens
     * @param int $index
     * @param int $lineNumber
     * @return null
     */
    public function getNextLineIndex(&$tokens, $index, $lineNumber)
    {
        $length = count($tokens);
        $currentIndex = $index + 1;
        while ($currentIndex < $length) {
            if (is_array($tokens[$currentIndex]) && $tokens[$currentIndex][2] > $lineNumber) {
                return $currentIndex;
            }
            $currentIndex++;
        }
        return null;
    }

    /**
     * Return the index of first token on the previous line
     *
     * @param array $tokens
     * @param int $index
     * @param int $lineNumber
     * @return null
     */
    public function getPrevLineIndex(&$tokens, $index, $lineNumber)
    {
        $currentIndex = $index - 1;
        while ($currentIndex >= 0) {
            if (is_array($tokens[$currentIndex]) && $tokens[$currentIndex][2] < $lineNumber) {
                return $currentIndex;
            }
            $currentIndex--;
        }
        return null;
    }


    /**
     * Return the index after method call
     *
     * @param array $tokens
     * @param int $index
     * @return int
     */
    public function skipMethodCall(array &$tokens, $index)
    {
        //find the first (, then find match )
        $nextIndex = $this->getNextIndexOf($tokens, $index, '(') + 1;

        $nestedLevel = 0; //used to skip nested (), can't handle quote though
        $found = false;
        while (!$found) {
            if (is_array($tokens[$nextIndex])) {
                $nextIndex++;
                continue;
            }
            if ($tokens[$nextIndex] == '(') {
                $nestedLevel++;
            }
            if ($tokens[$nextIndex] == ')') {
                if ($nestedLevel == 0) {
                    break;
                } else {
                    $nestedLevel--;
                }
            }
            $nextIndex++;
        }

        return $nextIndex;
    }

    /**
     * Get the argument of a method call
     * use starting index as the object or the method itself
     *
     * @param mixed[] $tokens
     * @param int $startingIndex
     * @param bool $trim
     * @return \Magento\Migration\Code\Processor\CallArgumentCollection
     */
    public function getCallArguments(array &$tokens, $startingIndex, $trim = true)
    {
        $startingIndex = $this->getNextIndexOf($tokens, $startingIndex, '(');
        $endingIndex = $this->skipMethodCall($tokens, $startingIndex);

        $nextIndex = $startingIndex + 1;
        $nestedLevel = 0; //used to skip nested (), can't handle quote though
        $paramIndexes = [];
        $prevIndex = $nextIndex;
        while ($nextIndex < $endingIndex) {
            if (is_array($tokens[$nextIndex])) {
                $nextIndex++;
                continue;
            }
            if ($tokens[$nextIndex] == ',' && $nestedLevel == 0) {
                $paramIndexes[] = ['from' => $prevIndex, 'to' => $nextIndex - 1];
                $prevIndex = $nextIndex + 1;
            }
            if ($tokens[$nextIndex] == '(' || $tokens[$nextIndex] == '[') {
                $nestedLevel++;
            }
            if ($tokens[$nextIndex] == ')' || $tokens[$nextIndex] == ']') {
                if ($nestedLevel == 0) {
                    break;
                } else {
                    $nestedLevel--;
                }
            }
            $nextIndex++;
        }
        //add the last or non comma param
        $paramIndexes[] = ['from' => $prevIndex, 'to' => $nextIndex - 1];
        //build arguments
        $argumentCollection = $this->callCollectionFactory->create();
        foreach ($paramIndexes as $key => $idx) {
            $tokenCollection = $this->tokenCollectionFactory->create();
            for ($i = $idx['from']; $i <= $idx['to']; $i++) {
                $tokenCollection->addToken($this->tokenFactory->create()->setToken($tokens[$i]), $i);
            }
            if ($trim) {
                $argumentCollection->addArgument($this->trimTokenArguments($tokenCollection), $key);
            } else {
                $argumentCollection->addArgument($tokenCollection, $key);
            }
        }
        return $argumentCollection;
    }

    /**
     * Trims the whitespaces for an array of arguments
     *
     * @param \Magento\Migration\Code\Processor\TokenArgumentCollection $tokens
     * @return \Magento\Migration\Code\Processor\TokenArgumentCollection
     */
    public function trimTokenArguments($tokens)
    {
        foreach ($tokens->getTokens() as $key => $idx) {
            /** @var  \Magento\Migration\Code\Processor\TokenArgument $idx */
            if ($idx->getType() == T_WHITESPACE) {
                $tokens->removeToken($key);
            }
        }
        return $tokens;
    }

    /**
     * Replaces between start and end index with tokens
     * match the start and end index with the function parantesis
     *
     * @param mixed[] $tokens
     * @param $index
     * @param \Magento\Migration\Code\Processor\TokenArgumentCollection $replacementTokens
     * @return $this
     */
    public function replaceCallArgumentsTokens(&$tokens, $index, $replacementTokens)
    {
        $indexStart = $this->getNextIndexOf($tokens, $index, '(');
        $indexEnd = $this->skipMethodCall($tokens, $index);

        if ($tokens[$indexStart] != '(' && $tokens[$indexStart] != ')') {
            $this->logger->warn(
                'Start and End index don\'t match parenthesis function call index boundaries ' . $tokens[$indexStart][2]
            );
            return $this;
        }

        $tokens[$indexStart + 1] = [T_CONSTANT_ENCAPSED_STRING, '', $indexStart + 1, 'T_CONSTANT_ENCAPSED_STRING'];
        foreach ($replacementTokens->getTokens() as $idx) {
            /** @var  \Magento\Migration\Code\Processor\TokenArgument $idx */
            $tokens[$indexStart + 1][1] .= $idx->getName();
        }

        for ($i = $indexStart + 2; $i < $indexEnd; $i++) {
            $tokens[$i] = '';
        }
        return $this;
    }
}
