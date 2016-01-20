<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class ConstructorHelper
{
    /**
     * @var array
     */
    protected $tokens;

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
     * @return void
     */
    public function setContext(array &$tokens)
    {
        $this->tokens = &$tokens;
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
     * @return array
     */
    public function injectArguments($variables)
    {
        $variables = array_values($variables);
        //add member variables
        //update argument list
        //update assignment

        $this->addMembers($variables);
        $orderedInjectedArguments = [];
        if (!$this->hasConstructor()) {
            $text = $this->generateConstructor($variables);
            $indexToInsert = 0 - $this->getConstructorIndex();
            $this->tokens[$indexToInsert][1] .= $text;
        } else {
            $orderedInjectedArguments = $this->addConstructorArguments($variables);
        }
        return $orderedInjectedArguments;
    }

    /**
     * @param array $variables
     * @return void
     */
    protected function addMembers($variables)
    {
        $firstFunctionIndex = $this->tokenHelper->getNextIndexOfTokenType($this->tokens, 0, T_FUNCTION);
        $firstFunctionIndex = $this->tokenHelper->getFunctionStartingIndex($this->tokens, $firstFunctionIndex);
        if (is_array($this->tokens[$firstFunctionIndex]) && $this->tokens[$firstFunctionIndex][0] == T_WHITESPACE) {
            $this->tokens[$firstFunctionIndex][1] = "";
        }

        $text = "\n";
        foreach ($variables as $variable) {
            $text .= "\n    /**\n     * @var " . $variable['type'] . "\n     */\n";
            $text .= "    protected $" . $variable['variable_name'] . ";\n";
        }
        $text .= "\n    ";
        $this->tokens[$firstFunctionIndex][1]
            = $this->tokens[$firstFunctionIndex][1] . $text . $this->tokens[$firstFunctionIndex][1];
        return;
    }

    /**
     * @param array $variables
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function addConstructorArguments($variables)
    {
        $orderedDiVariables = [];
        $startIndex = $this->getConstructorIndex();
        $existingArguments = $this->tokenHelper->getFunctionArguments($this->tokens, $startIndex);

        $text = '';
        if (empty($existingArguments)) {
            $startIndex = $this->tokenHelper->getNextIndexOfSimpleToken($this->tokens, $startIndex, '(');

            foreach ($variables as $variable) {
                $text .= "\n        ";
                if (isset($variable['type'])) {
                    $text .= $variable['type'];
                }
                $text .= ' $' . $variable['variable_name'] . ',';
            }
            $text = trim($text, ',');
            $text .= "\n    ";

            $this->tokens[$startIndex] .= $text;
        } else {
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

            if ($firstOptionalVariableName !== null) {
                foreach ($variables as $variable) {
                    if (isset($variable['type'])) {
                        $text .= $variable['type'];
                    }
                    $text .= ' $' . $variable['variable_name'] . ',';
                    $text .= "\n        ";
                }
                if ($count == 0) {
                    //all arguments are optional
                    $indexToInsert = $this->tokenHelper->getNextIndexOfSimpleToken($this->tokens, $startIndex, '(');
                    if (is_array($this->tokens[$indexToInsert + 1])
                        && $this->tokens[$indexToInsert + 1][0] == T_WHITESPACE) {
                        $indexToInsert++;
                        $this->tokens[$indexToInsert][1] .= $text;
                    } else {
                        $text = "\n        " . $text;
                        $this->tokens[$indexToInsert] .= $text;
                    }
                } else {
                    $variableIndex = $this->tokenHelper->getNextIndexOfTokenType(
                        $this->tokens,
                        $startIndex,
                        T_VARIABLE,
                        $existingArguments[$count - 1]->getName()
                    );
                    $indexToInsert = $this->tokenHelper->getNextIndexOfSimpleToken($this->tokens, $variableIndex, ',');
                    $indexToInsert = $this->tokenHelper
                        ->getNextIndexOfTokenType($this->tokens, $indexToInsert, T_WHITESPACE);
                    $this->tokens[$indexToInsert][1] = $this->tokens[$indexToInsert][1] . $text;
                }
            } else {
                //insert after all arguments
                $text .= "\n        ";
                $count = count($variables);
                for ($i = 0; $i < $count; $i++) {
                    $variable = $variables[$i];
                    if (isset($variable['type'])) {
                        $text .= $variable['type'];
                    }
                    $text .= ' $' . $variable['variable_name'];
                    if ($i < $count - 1) {
                        $text .= ",\n        ";
                    } else {
                        $text .= "\n    ";
                    }
                }
                $variableIndex = $this->tokenHelper->getNextIndexOfTokenType(
                    $this->tokens,
                    $startIndex,
                    T_VARIABLE,
                    $existingArguments[$numArguments - 1]->getName()
                );
                $indexToInsert = $this->tokenHelper->getNextIndexOfSimpleToken(
                    $this->tokens,
                    $variableIndex,
                    ')'
                );
                if (is_array($this->tokens[$indexToInsert - 1])
                    && $this->tokens[$indexToInsert - 1][0] == T_WHITESPACE) {
                    $indexToInsert = $indexToInsert - 1;
                    $this->tokens[$indexToInsert][1] = '';
                    $this->tokens[$indexToInsert][1] = ',' . $this->tokens[$indexToInsert][1];
                    $this->tokens[$indexToInsert][1] .= $text;
                } else {
                    $this->tokens[$indexToInsert] = ',' . $text . ')';
                }
            }
        }

        $startIndex = $this->tokenHelper->getNextIndexOfSimpleToken($this->tokens, $startIndex, '{');
        $text = '';
        foreach ($variables as $variable) {
            $orderedDiVariables[$variable['variable_name']]  = $variable;
            $text .= "\n        \$this->" . $variable['variable_name'] . ' = $' . $variable['variable_name'] . ';';
        }
        $this->tokens[$startIndex] = $this->tokens[$startIndex] . $text;
        return $orderedDiVariables;
    }

    /**
     * @param array $variables
     * @return string
     */
    protected function generateConstructor($variables)
    {
        //TODO: update doc
        $text = "public function __construct(\n";

        $isFirst = true;
        foreach ($variables as $variable) {
            if (!$isFirst) {
                $text .= ",\n";
            } else {
                $isFirst = false;
            }
            $text .= "        ";

            if (isset($variable['type'])) {
                $text .= $variable['type'];
                $text .= ' $';
            }
            $text .= $variable['variable_name'];
        }

        $text .= "\n    ) {\n";
        foreach ($variables as $variable) {
            $text .= "        ";
            $text .= '$this->' . $variable['variable_name'] . ' = $' . $variable['variable_name'] . ";\n";
        }

        $text .= "    }\n    ";

        return $text;
    }

    /**
     * @return string|null
     */
    public function getParentClass()
    {
        $index = 0;
        $indexExtend = $this->tokenHelper->getNextIndexOfTokenType($this->tokens, $index, T_EXTENDS);
        if ($indexExtend === null) {
            return null;
        }

        $indexParentClass = $this->tokenHelper->getNextIndexOfTokenType($this->tokens, $indexExtend, T_STRING);
        return $this->tokens[$indexParentClass][1];
    }

    /**
     * @return int the starting index of constructor, or negative value of index to insert constructor
     */
    public function getConstructorIndex()
    {
        $index = $this->tokenHelper->getNextIndexOfSimpleToken($this->tokens, 0, '{');

        $length = count($this->tokens);
        //check whether function __construct is defined

        $firstFunctionIndex = null;
        while ($index < $length) {
            if (!is_array($this->tokens[$index]) || $this->tokens[$index][0] != T_FUNCTION) {
                $index++;
                continue;
            }

            $functionNameIndex = $this->tokenHelper->getNextIndexOfTokenType($this->tokens, $index, T_STRING);
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
