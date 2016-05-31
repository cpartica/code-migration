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
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ClassProcessor implements \Magento\Migration\Code\ProcessorInterface
{
    /**
     * @var string $filePath
     */
    protected $filePath;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @var \Magento\Migration\Code\Processor\ConstructorHelperFactory
     */
    protected $constructorHelperFactory;

    /**
     * @var \Magento\Migration\Code\Processor\NamingHelper
     */
    protected $namingHelper;

    /**
     * M1 classes, references to which are to be left unchanged
     *
     * @var array
     */
    protected $skippedClasses = [];

    /**
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Code\Processor\ConstructorHelperFactory $constructorHelperFactory
     * @param TokenHelper $tokenHelper
     * @param \Magento\Migration\Code\Processor\NamingHelper $namingHelper
     * @param array $skippedClasses
     */
    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\ConstructorHelperFactory $constructorHelperFactory,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Processor\NamingHelper $namingHelper,
        array $skippedClasses = []
    ) {
        $this->logger = $logger;
        $this->constructorHelperFactory = $constructorHelperFactory;
        $this->tokenHelper = $tokenHelper;
        $this->namingHelper = $namingHelper;
        $this->skippedClasses = $skippedClasses;
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
     * Take an array of tokens as input, return modified array of tokens
     *
     * @param array $tokens
     * @return array
     */
    public function process(array $tokens)
    {
        $nameSpace = $this->getNameSpace($tokens);

        if ($nameSpace !== null) {
            $this->logger->warn('The class already uses namespace');
        } else {
            $this->processNamespace($tokens);
        }

        // process the variable type declaration in function definition
        $this->processArgumentType($tokens);

        //process static class reference, e.g., CLASSNAME::CONSTANT_NAME
        $this->processStaticClassReference($tokens);

        //process instanceof, e.g., instance Mage_Type
        $this->processInstanceOf($tokens);

        // process constructor change due to class inheritance
        $this->processExtends($tokens);

        $tokens = $this->tokenHelper->refresh($tokens);
        return $tokens;
    }

    /**
     * @param array $tokens
     * @return $this
     */
    protected function processExtends(array &$tokens)
    {
        $extendIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_EXTENDS);
        if ($extendIndex !== null) {
            $parentClassIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, $extendIndex, T_STRING);
            if ($parentClassIndex === null) {
                $this->logger->warn("Expected class name not found after extends keyword");
                return $this;
            }
            $parentClassName = $tokens[$parentClassIndex][1];

            $mappedClass = $this->getM2ClassName($parentClassName);
            if ($mappedClass) {
                $tokens[$parentClassIndex][1] = $mappedClass;

                try {
                    $reflectionClass = new \ReflectionClass($mappedClass);
                    $parentConstructor = $reflectionClass->getConstructor();
                    if ($parentConstructor) {
                        $this->extendConstructor($tokens, $parentConstructor);
                    }
                } catch (\Exception $e) {
                    $this->logger->warn("Failed to load parent class: " . $mappedClass);
                }
            } else {
                //TODO: add parent::__constructor even if the class couldn't be mapped and add a code comment
                $this->logger->warn(
                    "Parent class is not mapped, constructor not converted," .
                    "parent::__constructor is not called: " . $parentClassName
                );
            }
        }

        return $this;
    }

    /**
     * @param string $m1ClassName
     * @return null|string
     */
    protected function getM2ClassName($m1ClassName)
    {
        if (in_array($m1ClassName, $this->skippedClasses)) {
            return null;
        }
        return $this->namingHelper->getM2ClassName($m1ClassName);
    }

    /**
     * @param array $tokens
     * @param \ReflectionMethod $constructor
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function extendConstructor(array &$tokens, \ReflectionMethod $constructor)
    {
        /** @var \Magento\Migration\Code\Processor\ConstructorHelper $contructorHelper */
        $contructorHelper = $this->constructorHelperFactory->create();
        $contructorHelper->setContext($tokens);

        $parameters = $constructor->getParameters();
        $count = count($parameters);

        $constructorIndex = $contructorHelper->getConstructorIndex();
        if ($constructorIndex < 0) {
            //insert a new constructor
            $constructorIndex = 0 - $constructorIndex;
            $this->insertConstructor($tokens, $constructorIndex, $constructor);
            return $this;
        }

        $this->modifyParentConstructorCall($tokens, $parameters, $constructorIndex);

        $startOfParameterList = $this->tokenHelper->getNextIndexOfSimpleToken($tokens, $constructorIndex, '(');
        $currentLine = $tokens[$startOfParameterList - 1][2];
        if (!is_array($tokens[$startOfParameterList + 1]) && $tokens[$startOfParameterList + 1] == ')') {
            $tokensToInsert = [];
            if ($count > 0) {
                $tokensToInsert[] = [T_WHITESPACE, "\n        ", $currentLine];
            }

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
                        $defaultValue = trim(var_export($defaultValue, true));
                    }
                    $tokensToInsert[] = [T_STRING, $defaultValue, $currentLine];
                }
                if ($parameter->getPosition() < $numParameters - 1) {
                    $tokensToInsert[] = ',';
                    $tokensToInsert[] = [T_WHITESPACE, "\n        ", $currentLine];
                } else {
                    $tokensToInsert[] = [T_WHITESPACE, "\n    ", $currentLine];
                }
            }
        } else {
            //Most of constructor in M1 do not take parameters
            //TODO: handle existing constructor parameters
            $this->logger->warn("Need to merge parameter from parent constructor manually");
            return $this;
        }

        $afterInsertion = array_slice($tokens, $startOfParameterList + 1);

        $tokens = array_merge(
            array_slice($tokens, 0, $startOfParameterList + 1),
            $tokensToInsert,
            $afterInsertion
        );

        return $this;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @param \ReflectionMethod $parentConstructor
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function insertConstructor(array &$tokens, $index, $parentConstructor)
    {
        $tokensToInsert = [];

        $parameters = $parentConstructor->getParameters();
        $count = count($parameters);

        $tokensToInsert[] = [T_WHITESPACE, "\n    ", 1]; //the line number is not important
        $tokensToInsert[] = [T_PUBLIC, "public", 1];
        $tokensToInsert[] = [T_WHITESPACE, " ", 1];
        $tokensToInsert[] = [T_FUNCTION, "function", 1];
        $tokensToInsert[] = [T_WHITESPACE, " ", 1];
        $tokensToInsert[] = [T_STRING, "__construct", 1];
        $tokensToInsert[] = '(';
        if ($count > 0) {
            $tokensToInsert[] = [T_WHITESPACE, "\n        ", 1];
        }

        for ($i = 0; $i < $count; $i++) {
            $isLast = ($i == $count - 1);

            $parameter = $parameters[$i];
            $parameterClass = $parameter->getClass();
            if ($parameterClass) {
                $tokensToInsert[] = [T_STRING, '\\' . $parameterClass->getName(), 1];
                $tokensToInsert[] = [T_WHITESPACE, ' ', 1];
            } elseif ($parameter->isArray()) {
                $tokensToInsert[] = [T_STRING, 'array', 1];
                $tokensToInsert[] = [T_WHITESPACE, ' ', 1];
            }
            $tokensToInsert[] = [T_VARIABLE, '$' . $parameter->getName(), 1];

            if ($parameter->isDefaultValueAvailable()) {
                $tokensToInsert[] = [T_WHITESPACE, ' ', 1];
                $tokensToInsert[] = '=';
                $tokensToInsert[] = [T_WHITESPACE, ' ', 1];
                $defaultValue = $parameter->getDefaultValue();
                if (is_array($defaultValue) && empty($defaultValue)) {
                    $defaultValue = '[]';
                } elseif ($defaultValue === null) {
                    $defaultValue = 'null';
                } else {
                    $defaultValue = trim(var_export($defaultValue, true));
                }
                $tokensToInsert[] = [T_STRING, $defaultValue, 1];
            }

            if (!$isLast) {
                $tokensToInsert[] = ',';
                $tokensToInsert[] = [T_WHITESPACE, "\n        ", 1];
            } else {
                $tokensToInsert[] = [T_WHITESPACE, "\n    ", 1];
            }
        }

        $tokensToInsert[] = ')';
        if ($count > 0) {
            $tokensToInsert[] = [T_WHITESPACE, " ", 1];
        } else {
            $tokensToInsert[] = [T_WHITESPACE, "\n    ", 1];
        }
        $tokensToInsert[] = '{';
        $tokensToInsert[] = [T_WHITESPACE, "\n        ", 1];

        $tokensToInsert[] = [T_STRING, "parent", 1];
        $tokensToInsert[] = [T_DOUBLE_COLON, "::", 1];
        $tokensToInsert[] = [T_STRING, "__construct", 1];
        $tokensToInsert[] = '(';
        if ($count > 0) {
            $tokensToInsert[] = [T_WHITESPACE, "\n            ", 1];
        }

        for ($i = 0; $i < $count; $i++) {
            $isLast = ($i == $count - 1);

            $parameter = $parameters[$i];
            $parameterName = $parameter->getName();

            $tokensToInsert[] = [T_VARIABLE, '$' . $parameterName, 1];
            if (!$isLast) {
                $tokensToInsert[] = ',';
                $tokensToInsert[] = [T_WHITESPACE, "\n            ", 1];
            } else {
                $tokensToInsert[] = [T_WHITESPACE, "\n        ", 1];
            }
        }

        $tokensToInsert[] = ')';
        $tokensToInsert[] = ';';
        $tokensToInsert[] = [T_WHITESPACE, "\n    ", 1];
        $tokensToInsert[] = '}';
        $tokensToInsert[] = [T_WHITESPACE, "\n", 1];

        $afterInsertion = array_slice($tokens, $index);

        $tokens = array_merge(
            array_slice($tokens, 0, $index),
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function modifyParentConstructorCall(array &$tokens, array $parameters, $constructorIndex)
    {
        $endOfConstructor = $this->tokenHelper->skipBlock($tokens, $constructorIndex);

        $found = false;
        $index = $this->tokenHelper->getNextIndexOfTokenType($tokens, $constructorIndex, T_DOUBLE_COLON);
        while (!$found && $index !== null && $index <= $endOfConstructor) {
            if (is_array($tokens[$index - 1]) && $tokens[$index - 1][1] == 'parent'
                && is_array($tokens[$index + 1]) && $tokens[$index + 1][1] == '__construct'
            ) {
                $found = true;
            } else {
                $index = $this->tokenHelper->getNextIndexOfTokenType($tokens, $index + 1, T_DOUBLE_COLON);
            }
        }

        $count = count($parameters);
        $tokensToInsert = [];
        if (!$found) {
            //add parent::__construct call
            $insertIndex = $this->tokenHelper->getNextIndexOfSimpleToken($tokens, $constructorIndex, '{');
            $currentLine = $tokens[$insertIndex - 1][2];
            $tokensToInsert[] = [T_WHITESPACE, "\n        ", $currentLine];
            $tokensToInsert[] = [T_STRING, 'parent', $currentLine];
            $tokensToInsert[] = [T_DOUBLE_COLON, '::', $currentLine];
            $tokensToInsert[] = [T_STRING, '__construct', $currentLine];
            $tokensToInsert[] = '(';
            if ($count > 0) {
                $tokensToInsert[] = [T_WHITESPACE, "\n            ", $currentLine];
            }
        } else {
            $insertIndex = $index + 2;
            $currentLine = $tokens[$index][2];
            if ($count > 0) {
                $tokensToInsert[] = [T_WHITESPACE, "\n            ", $currentLine];
            }
        }

        $numParameters = count($parameters);
        foreach ($parameters as $parameter) {
            $tokensToInsert[] = [T_VARIABLE, '$' . $parameter->getName(), $currentLine];
            if ($parameter->getPosition() < $numParameters - 1) {
                $tokensToInsert[] = ',';
                $tokensToInsert[] = [T_WHITESPACE, "\n            ", $currentLine];
            } else {
                $tokensToInsert[] = [T_WHITESPACE, "\n        ", $currentLine];
            }
        }

        if (!$found) {
            $tokensToInsert[] = ')';
            $tokensToInsert[] = ';';
            if ($count > 0) {
                $tokensToInsert[] = [T_WHITESPACE, "\n", $currentLine];
            }
        }
        $afterInsertion = array_slice($tokens, $insertIndex + 1);

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
        if ($classIndex === null) {
            $classIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_INTERFACE);
        }

        if ($classIndex === null) {
            return $this;
        } else {
            $classNameIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, $classIndex, T_STRING);
            $className = $tokens[$classNameIndex][1];
            $m2ClassName = $this->getM2ClassName($className);
            list($nameSpace, $shortClassName) = $this->parseNamespaceClassName($m2ClassName);
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
            $tokens[$index][1] = $tokens[$index][1] . "namespace " . $nameSpace . ";\n\n";
        } else {
            $tokens[$openTagIndex][1] = $tokens[$openTagIndex][1] . "namespace " . $nameSpace . ";\n\n";
        }

        return $this;
    }

    /**
     * @param string $className
     * @return array
     */
    protected function parseNamespaceClassName($className)
    {
        if (preg_match('/\\\\?(.+)\\\\(.+?)$/', $className, $parts)) {
            list(, $namespace, $class) = $parts;
        } else {
            $namespace = '';
            $class = $className;
        }
        return [$namespace, $class];
    }

    /**
     * @param array $tokens
     * @return null|string
     */
    protected function getNameSpace(array &$tokens)
    {
        $indexNamespace = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_NAMESPACE);
        if ($indexNamespace === null) {
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
        while ($functionIndex !== null) {
            $arguments = $this->tokenHelper->getFunctionArguments($tokens, $functionIndex);

            $currentIndex = $functionIndex;
            foreach ($arguments as $argument) {
                if ($argument->getType() !== null) {
                    $typeName = $argument->getType();
                    $mappedClass = $this->getM2ClassName($typeName);
                    if ($mappedClass) {
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
        while ($doubleColonIndex !== null) {
            $prevToken = $tokens[$doubleColonIndex - 1];
            if (is_array($prevToken) && $prevToken[0] == T_STRING && !$this->isSelfReference($prevToken[1])) {
                $mappedClass = $this->getM2ClassName($prevToken[1]);
                if ($mappedClass) {
                    $tokens[$doubleColonIndex - 1][1] = $mappedClass;
                }
            }
            $doubleColonIndex =
                $this->tokenHelper->getNextIndexOfTokenType($tokens, $doubleColonIndex + 1, T_DOUBLE_COLON);
        }
        return $this;
    }

    /**
     * @param string $referenceName
     * @return bool
     */
    protected function isSelfReference($referenceName)
    {
        return in_array($referenceName, ['self', 'parent', 'static']);
    }

    /**
     * @param array $tokens
     * @return $this
     */
    public function processInstanceOf(array &$tokens)
    {
        $currentIndex = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_INSTANCEOF);
        while ($currentIndex !== null) {
            $nextTokenIndex = $this->tokenHelper->getNextTokenIndex($tokens, $currentIndex);
            if ($nextTokenIndex === null) {
                return $this;
            }
            $nextToken = $tokens[$nextTokenIndex];
            if (is_array($nextToken) && $nextToken[0] == T_STRING) {
                $mappedClass = $this->getM2ClassName($nextToken[1]);
                if ($mappedClass) {
                    $tokens[$nextTokenIndex][1] = $mappedClass;
                }
            }
            $currentIndex =
                $this->tokenHelper->getNextIndexOfTokenType($tokens, $currentIndex + 1, T_INSTANCEOF);
        }
        return $this;
    }
}
