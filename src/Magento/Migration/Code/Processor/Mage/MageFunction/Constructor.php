<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class Constructor
{
    /**
     * @var array
     */
    protected $tokens;

    /**
     * @var int
     */
    protected $index;

    /**
     * @var \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory
     */
    protected $argumentFactory;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @var bool
     */
    protected $parsed = false;

    /** @var string  */
    protected $parentClassName;

    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory $argumentFactory
    ) {
        $this->tokenHelper = $tokenHelper;
        $this->logger = $logger;
        $this->argumentFactory = $argumentFactory;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return void
     */
    public function setContext(array &$tokens, $index = 0)
    {
        $this->tokens = &$tokens;
        $this->index = $index;
    }

    /**
     * @return bool
     */
    public function hasConstructor()
    {
        return $this->getConstructorIndex() > 0;
    }

    /**
     * @param array $variables
     * @return string
     */
    public function injectArguments($variables)
    {
        //update doc
        //update argument list
        //update assignment

        $this->addMembers($variables);

        if (!$this->hasConstructor()) {
            $text = $this->generateConstructor($variables);
            $indexToInsert = 0 - $this->getConstructorIndex();
            $this->tokens[$indexToInsert][1] .= $text;
        } else {
            $this->addConstructorArguments($variables);
        }
    }

    /**
     * @param array $variables
     */
    protected function addMembers($variables)
    {
        $firstFunctionIndex = $this->tokenHelper->getNextIndexOfType($this->tokens, 0, T_FUNCTION);
        $firstFunctionIndex = $this->tokenHelper->getFunctionStartingIndex($this->tokens, $firstFunctionIndex);

        $text = "\n";
        foreach ($variables as $variable) {
            $text .= "\n\t/** \n\t * @var " . $variable['type'] . "\n\t */\n";
            $text .= "\tprotected $" . $variable['variable_name'] . ";\n";
        }
        $this->tokens[$firstFunctionIndex][1]
            = $this->tokens[$firstFunctionIndex][1] . $text . $this->tokens[$firstFunctionIndex][1];
        return;
    }

    /**
     * @param array $variables
     */
    protected function addConstructorArguments($variables)
    {
        $startIndex = $this->getConstructorIndex();
        $existingArguments = $this->tokenHelper->getFunctionArguments($this->tokens, $startIndex);

//        $text = '';
//        foreach ($variables as $variable) {
//            $text .= "\n\t\t";
//            if (isset($variable['type'])) {
//                $text .= $variable['type'];
//            }
//            $text .= ' $' . $variable['variable_name'] . ',';
//        }
//
        if (empty($existingArguments)) {
            $startIndex = $this->tokenHelper->getNextIndexOf($this->tokens, $startIndex, '(');

            $text = '';
            foreach ($variables as $variable) {
                $text .= "\n\t\t";
                if (isset($variable['type'])) {
                    $text .= $variable['type'];
                }
                $text .= ' $' . $variable['variable_name'] . ',';
            }
            $text = trim($text, ',');
            $text .= "\n";

            $this->tokens[$startIndex] .= $text;
        } else {
            //find the last required argument
            $lastRequiredArgumentName = null;
            $count = 0;
            $numArguments = count($existingArguments);
            $firstOptionalVariableName = null;
            foreach ($existingArguments as $argument) {
                if ($argument->isOptional()) {
                    $firstOptionalVariableName = $argument->getName();
                    break;
                }
                $count++;
            }

            if ($firstOptionalVariableName != null) {
                $text = '';
                foreach ($variables as $variable) {
                    if (isset($variable['type'])) {
                        $text .= $variable['type'];
                    }
                    $text .= ' $' . $variable['variable_name'] . ',';
                    $text .= "\n\t\t";
                }
                if ($count == 0) {
                    $indexToInsert = $this->tokenHelper->getNextIndexOf($this->tokens, $startIndex, '(');
                    if (is_array($this->tokens[$indexToInsert + 1])
                        && $this->tokens[$indexToInsert + 1][0] == T_WHITESPACE) {
                        $indexToInsert++;
                        $this->tokens[$indexToInsert][1] .= $text;
                    } else {
                        $text = "\n\t\t" . $text;
                        $this->tokens[$indexToInsert] .= $text;
                    }
                } else {
                    $variableIndex = $this->tokenHelper->getNextIndexOfType(
                        $this->tokens,
                        $startIndex,
                        T_VARIABLE,
                        $existingArguments[$count - 1]->getName()
                    );
                    $indexToInsert = $this->tokenHelper->getNextIndexOf($this->tokens, $variableIndex, ',');
                    $indexToInsert = $this->tokenHelper
                        ->getNextIndexOfType($this->tokens, $indexToInsert, T_WHITESPACE);
                    $this->tokens[$indexToInsert][1] = $this->tokens[$indexToInsert][1] . $text;
                }
            } else {
                //insert after all arguments
                $text = '';
                $text .= "\n\t\t";
                foreach ($variables as $variable) {
                    if (isset($variable['type'])) {
                        $text .= $variable['type'];
                    }
                    $text .= ' $' . $variable['variable_name'] . ',';
                }
                $variableIndex = $this->tokenHelper->getNextIndexOfType(
                    $this->tokens,
                    $startIndex,
                    T_VARIABLE,
                    $existingArguments[$numArguments - 1]->getName()
                );
                $indexToInsert = $this->tokenHelper->getNextIndexOfType($this->tokens, $variableIndex, T_WHITESPACE);

                $this->tokens[$indexToInsert][1] = ',' . $this->tokens[$indexToInsert][1];
                $text = trim($text, ',');
                $text .= "\n\t\t";
                $this->tokens[$indexToInsert][1] .= $text;
            }
        }


        $startIndex = $this->tokenHelper->getNextIndexOf($this->tokens, $startIndex, '{');
        $text = '';
        foreach ($variables as $variable) {
            $text .= "\n\t\t\$this->" . $variable['variable_name'] . ' = $' . $variable['variable_name'] . ';';
        }
        $this->tokens[$startIndex] = $this->tokens[$startIndex] . $text;
    }

    protected function generateConstructor($variables)
    {
        //TODO: update doc
        $text = "\n\tpublic function __construct(\n";

        $isFirst = true;
        foreach ($variables as $variable) {
            if (!$isFirst) {
                $text .= ",\n";
            } else {
                $isFirst = false;
            }
            $text .= "\t\t";

            if (isset($variable['type'])) {
                $text .= $variable['type'];
                $text .= ' $';
            }
            $text .= $variable['variable_name'];
        }

        $text .= "\n\t) {\n";
        foreach ($variables as $variable) {
            $text .= "\t\t";
            $text .= '$this->' . $variable['variable_name'] . ' = $' . $variable['variable_name'] . ";\n";
        }

        $text .= "\t}\n\t";

        return $text;
    }

    public function addInheritedArguments()
    {
        //get the arguments for parent class constructor
        //add those variable to constructor
        //call parent constructor

    }

    public function getParentClass()
    {
        $index = 0;
        $indexExtend = $this->tokenHelper->getNextIndexOfType($this->tokens, $index, T_EXTENDS);
        if ($indexExtend === null) {
            return null;
        }

        $indexParentClass = $this->tokenHelper->getNextIndexOfType($this->tokens, $indexExtend, T_STRING);
        return $this->tokens[$indexParentClass][1];
    }

    /**
     * @return int the starting index of constructor, or negative value of index to insert constructor
     */
    public function getConstructorIndex()
    {
        $index = $this->tokenHelper->getNextIndexOf($this->tokens, 0, '{');

        $length = count($this->tokens);
        //check whether function __construct is defined

        $firstFunctionIndex = null;
        while ($index < $length) {
            if (!is_array($this->tokens[$index]) || $this->tokens[$index][0] != T_FUNCTION) {
                $index++;
                continue;
            }

            $functionNameIndex = $this->tokenHelper->getNextIndexOfType($this->tokens, $index, T_STRING);
            if ($this->tokens[$functionNameIndex][1] != '__construct') {
                if (!$firstFunctionIndex) {
                    //remember the index to insert constructor;
                    $firstFunctionIndex = $this->tokenHelper->getFunctionStartingIndex(
                        $this->tokens,
                        $functionNameIndex
                    );
                }
                $index++;
                continue;
            }

            return $this->tokenHelper->getFunctionStartingIndex($this->tokens, $functionNameIndex);
        }
        return 0 - $firstFunctionIndex;
    }
}
