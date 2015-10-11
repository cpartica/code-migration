<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

/**
 * Class ClassProcessor
 * @package Magento\Migration\Code\Processor
 *
 * This class processes name spaces and class inheritance
 */
class ClassProcessor implements \Magento\Migration\Code\ProcessorInterface
{
    /**
     * @var \Magento\Migration\Mapping\ClassMapping
     */
    protected $classMap;
    /**
     * @var \Magento\Migration\Mapping\Alias
     */
    protected $aliasMap;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @var \Magento\Migration\Mapping\Context
     */
    protected $context;

    /**
     * @var \Magento\Migration\Code\Processor\Mage\MageFunction\ConstructorFactory
     */
    protected $constructorHelperFactory;

    public function __construct(
        \Magento\Migration\Mapping\ClassMapping $classMap,
        \Magento\Migration\Mapping\Alias $aliasMap,
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Mapping\Context $context,
        \Magento\Migration\Code\Processor\Mage\MageFunction\ConstructorFactory $constructorHelperFactory,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
    ) {
        $this->classMap = $classMap;
        $this->aliasMap = $aliasMap;
        $this->logger = $logger;
        $this->context = $context;
        $this->constructorHelperFactory = $constructorHelperFactory;
        $this->tokenHelper = $tokenHelper;
    }

    /**
     * Take an array of tokens as input, return modified array of tokens
     *
     * @param array $tokens
     * @return array
     */
    public function process(array $tokens)
    {
        $nameSpace = $this->getNameSpace($tokens);

        if ($nameSpace != null) {
            $this->logger->warn('The class already uses namespace');
        } else {
            $this->processNamespace($tokens);
        }

        // process the variable type declaration in function definition
        $this->processArgumentType($tokens);

        //process static class reference, e.g., CLASSNAME::CONSTANT_NAME
        $this->processStaticClassReference($tokens);

        // process constructor change due to class inheritance
        $this->processExtends($tokens);
        return $tokens;
    }

    /**
     * @param array $tokens
     * @return $this
     */
    protected function processExtends(array &$tokens)
    {
        $extendIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_EXTENDS);
        if ($extendIndex != null) {
            $parentClassIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, $extendIndex, T_STRING);
            $parentClassName = $tokens[$parentClassIndex][1];

            $mappedClass = $this->classMap->mapM1Class($parentClassName);
            if ($mappedClass) {
                $tokens[$parentClassIndex][1] = $mappedClass;

                try {
                    if ($mappedClass == 'obsolete') {
                        $this->logger->warn('The parent class ' . $parentClassName . ' is obsolete');
                    }
                    if (!class_exists($mappedClass)) {
                        $this->logger->warn('Can not find the class ' . $mappedClass);
                        return $this;
                    }
                    $reflectionClass = new \ReflectionClass($mappedClass);
                    $constructor = $reflectionClass->getConstructor();
                    if ($constructor == null) {
                        return $this;
                    }
                    $this->extendConstructor($tokens, $constructor);
                } catch (\Exception $e) {
                    $this->logger->warn("Failed to load parent class: " . $mappedClass);
                    return $this;
                }
            }
        }

        return $this;
    }

    protected function extendConstructor(array &$tokens, \ReflectionMethod $constructor)
    {
        /** @var \Magento\Migration\Code\Processor\Mage\MageFunction\Constructor $contructorHelper */
        $contructorHelper = $this->constructorHelperFactory->create();
        $contructorHelper->setContext($tokens);

        $constructorIndex = $contructorHelper->getConstructorIndex();
        if ($constructorIndex < 0) {
            return $this;
        }

        $parameters = $constructor->getParameters();
        if (empty($parameters)) {
            return $this;
        }

        $this->modifyParentConstructorCall($tokens, $parameters, $constructorIndex);

        $startOfParameterList = $this->tokenHelper->getNextIndexOfSimpleToken($tokens, $constructorIndex, '(');
        $startingLine = $currentLine = $tokens[$startOfParameterList - 1][2];
        if (!is_array($tokens[$startOfParameterList + 1]) && $tokens[$startOfParameterList + 1] == ')') {
            $tokensToInsert = [];
            $tokensToInsert[] = [T_WHITESPACE, "\n\t\t", $currentLine++];

            $numParameters = count($parameters);
            foreach ($parameters as $parameter) {
                $parameterClass = $parameter->getClass();
                if ($parameterClass) {
                    $tokensToInsert[] = [T_STRING, '\\' . $parameterClass->getName(), $currentLine];
                    $tokensToInsert[] = [T_WHITESPACE, ' ', $currentLine];
                } elseif ($parameter->isArray()) {
                    $tokensToInsert[] = [T_STRING, 'array', $currentLine];
                    $tokensToInsert[] = [T_WHITESPACE, ' ', $currentLine];
                }
                $tokensToInsert[] = [T_VARIABLE, '$' . $parameter->getName(), $currentLine];
                if ($parameter->isDefaultValueAvailable()) {
                    $tokensToInsert[] = [T_WHITESPACE, ' ', $currentLine];
                    $tokensToInsert[] = '=';
                    $tokensToInsert[] = [T_WHITESPACE, ' ', $currentLine];
                    $defaultValue = $parameter->getDefaultValue();
                    if ($defaultValue === null) {
                        $defaultValue = 'null';
                    } elseif (is_array($defaultValue) && empty($defaultValue)) {
                        $defaultValue = '[]';
                    } else {
                        //TODO: how to handle this?
                    }
                    $tokensToInsert[] = [T_STRING, $defaultValue, $currentLine];
                }
                if ($parameter->getPosition() < $numParameters - 1) {
                    $tokensToInsert[] = ',';
                }
                $tokensToInsert[] = [T_WHITESPACE, "\n\t\t", $currentLine++];
            }
        } else {
            //Most of constructor in M1 do not take parameters
            //TODO: handle existing constructor parameters
            return $this;
        }

        $numInsertedLines = $currentLine - $startingLine;
        $afterInsertion = array_slice($tokens, $startOfParameterList + 1);
        for ($i = 0; $i < count($afterInsertion); $i++) {
            if (is_array($afterInsertion[$i])) {
                $afterInsertion[$i][2] += $numInsertedLines;
            }
        }

        $tokens = array_merge(
            array_slice($tokens, 0, $startOfParameterList + 1),
            $tokensToInsert,
            $afterInsertion
        );

        return $this;
    }

    /**
     * @param array $tokens
     * @param \ReflectionParameter[] $parameters
     * @param int $constructorIndex
     * @return $this
     */
    protected function modifyParentConstructorCall(array &$tokens, array $parameters, $constructorIndex)
    {
        $endOfConstructor = $this->tokenHelper->skipBlock($tokens, $constructorIndex);

        $found = false;
        $index = $this->tokenHelper->getNextIndexOfTokenType($tokens, $constructorIndex, T_DOUBLE_COLON);
        while (!$found && $index != null && $index <= $endOfConstructor) {
            if (is_array($tokens[$index - 1]) && $tokens[$index - 1][1] == 'parent'
                && is_array($tokens[$index + 1]) && $tokens[$index + 1][1] == '__construct'
            ) {
                $found = true;
            } else {
                $index = $this->tokenHelper->getNextIndexOfTokenType($tokens, $index + 1, T_DOUBLE_COLON);
            }
        }

        $tokensToInsert = [];
        if (!$found) {
            //add parent::__construct call
            $insertIndex = $this->tokenHelper->getNextIndexOfSimpleToken($tokens, $constructorIndex, '{');
            $startingLine = $currentLine = $tokens[$insertIndex - 1][2];
            $tokensToInsert[] = [T_WHITESPACE, "\n\t\t", $currentLine++];
            $tokensToInsert[] = [T_STRING, 'parent', $currentLine];
            $tokensToInsert[] = [T_DOUBLE_COLON, '::', $currentLine];
            $tokensToInsert[] = [T_STRING, '__construct', $currentLine];
            $tokensToInsert[] = '(';
            $tokensToInsert[] = [T_WHITESPACE, "\n\t\t\t", $currentLine++];
        } else {
            $insertIndex = $index + 2;
            $startingLine = $currentLine = $tokens[$index][2];
            $tokensToInsert[] = [T_WHITESPACE, "\n\t\t\t", $currentLine++];
        }

        $numParameters = count($parameters);
        foreach ($parameters as $parameter) {
            $tokensToInsert[] = [T_VARIABLE, '$' . $parameter->getName(), $currentLine];
            if ($parameter->getPosition() < $numParameters - 1) {
                $tokensToInsert[] = ',';
            }
            $tokensToInsert[] = [T_WHITESPACE, "\n\t\t\t", $currentLine++];
        }

        if (!$found) {
            $tokensToInsert[] = ')';
            $tokensToInsert[] = ';';
            $tokensToInsert[] = [T_WHITESPACE, "\n\t\t\t", $currentLine++];
        }
        $numInsertedLines = $currentLine - $startingLine;

        $afterInsertion = array_slice($tokens, $insertIndex + 1);
        for ($i = 0; $i < count($afterInsertion); $i++) {
            if (is_array($afterInsertion[$i])) {
                $afterInsertion[$i][2] += $numInsertedLines;
            }
        }

        $tokens = array_merge(
            array_slice($tokens, 0, $insertIndex + 1),
            $tokensToInsert,
            $afterInsertion
        );
        return $this;
    }

    /**
     * @param array $tokens
     * @return $this
     */
    protected function processNamespace(array &$tokens)
    {
        $classIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_CLASS);
        if ($classIndex == null) {
            $classIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_INTERFACE);
        }

        if ($classIndex == null) {
            return $this;
        } else {
            $classNameIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, $classIndex, T_STRING);
            $className = $tokens[$classNameIndex][1];
            $parts = explode('_', $className);
            $shortClassName = array_pop($parts);
            $nameSpace = implode('\\', $parts);
            $tokens[$classNameIndex][1] = $shortClassName;
        }

        $openTagIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_OPEN_TAG);
        if ($openTagIndex === null) {
            $this->logger->error('Can not find the open tag');
            return $this;
        }

        $index = $this->tokenHelper->getNextLineIndex($tokens, $openTagIndex, $tokens[$openTagIndex][2]);
        if (is_array($tokens[$index]) && $tokens[$index][0] == T_DOC_COMMENT) {
            //skip the first doc block
            $index = $this->tokenHelper->getNextLineIndex($tokens, $index, $tokens[$index][2]);
        }

        //find the previous whitespace and append the namespace to to
        $index = $this->tokenHelper->getPrevIndexOfTokenType($tokens, $index, T_WHITESPACE);
        $tokens[$index][1] = $tokens[$index][1] . 'namespace ' . $nameSpace . ";\n\n";

        return $this;
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
            $indexNamespace = $this->tokenHelper->getNextIndexOfTokenType($tokens, $indexNamespace, T_STRING);
            return $tokens[$indexNamespace][1];
        }
    }

    /**
     * @param array $tokens
     * @return $this
     */
    protected function processArgumentType(array &$tokens)
    {
        $functionIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_FUNCTION);
        while ($functionIndex != null) {
            $arguments = $this->tokenHelper->getFunctionArguments($tokens, $functionIndex);

            $currentIndex = $functionIndex;
            foreach ($arguments as $argument) {
                if ($argument->getType() !== null) {
                    $typeName = $argument->getType();
                    if (strpos($typeName, 'Mage_') === 0 || strpos($typeName, 'Varien_') === 0) {
                        $mappedClass = $this->classMap->mapM1Class($typeName);
                        if ($mappedClass !== null && $mappedClass != 'obsolete') {
                            $currentIndex = $this->tokenHelper->getNextIndexOfTokenType(
                                $tokens,
                                $currentIndex + 1,
                                T_STRING,
                                $typeName
                            );
                            $tokens[$currentIndex][1] = $mappedClass;
                        }
                    }
                }
            }
            $functionIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, $functionIndex + 1, T_FUNCTION);
        }
        return $this;
    }

    /**
     * @param array $tokens
     * @return $this
     */
    public function processStaticClassReference(array &$tokens)
    {
        $doubleColonIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_DOUBLE_COLON);
        while ($doubleColonIndex != null) {
            $prevToken = $tokens[$doubleColonIndex - 1];
            if (is_array($prevToken) && $prevToken[0] == T_STRING
                && strpos($prevToken[1], 'Mage_') === 0 || strpos($prevToken[1], 'Varien_')
            ) {
                $mappedClass = $this->classMap->mapM1Class($prevToken[1]);
                if ($mappedClass !== null && $mappedClass != 'obsolete') {
                    $prevToken[1] = $mappedClass;
                }
            }
            $doubleColonIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, $doubleColonIndex + 1, T_FUNCTION);
        }
        return $this;
    }
}
