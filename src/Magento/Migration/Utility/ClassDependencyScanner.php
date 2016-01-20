<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Utility;

class ClassDependencyScanner
{
    const CONTEXT_EXTENDS = 'extends';
    const CONTEXT_IMPLEMENTS = 'implements';
    const CONTEXT_HELPER = 'helper';
    const CONTEXT_GET_MODEL = 'getModel';
    const CONTEXT_GET_SINGLETON = 'getSingleton';
    const CONTEXT_GET_RESOURCE_SINGLETON = 'getResourceSingleton';
    const CONTEXT_GET_RESOURCE_MODEL = 'getResourceModel';
    const CONTEXT_STATIC_METHOD_CALL = 'static';
    const CONTEXT_NEW = 'new';

    /**
     * @var \Magento\Migration\Mapping\ClassMapping
     */
    protected $classMapper;

    /**
     * @var \Magento\Migration\Mapping\Alias
     */
    protected $aliasMapper;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Migration\Mapping\ClassMapping $classMapper
     * @param \Magento\Migration\Mapping\Alias $aliasMapper
     * @param \Magento\Migration\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Migration\Mapping\ClassMapping $classMapper,
        \Magento\Migration\Mapping\Alias $aliasMapper,
        \Magento\Migration\Logger\Logger $logger
    ) {
        $this->classMapper = $classMapper;
        $this->aliasMapper = $aliasMapper;
        $this->logger = $logger;
    }

    /**
     * @param string $classFile
     * @return array list of classes referenced in the given class file
     */
    public function getClassReferenceByFile($classFile)
    {
        $content = file_get_contents($classFile);
        $tokens = token_get_all($content);

        return $this->getClassReference($tokens);
    }

    /**
     * @param array $tokens
     * @return array
     */
    public function getClassReference(array $tokens)
    {
        $referenceClasses = [];
        for ($i = 0; $i < count($tokens); $i++) {
            if (is_array($tokens[$i])) {
                $tokens[$i][3] = token_name($tokens[$i][0]);
            }
        }
        $processors = [
            'processExtends',
            'processImplements',
            'processStaticMethodCall',
            'processMageHelper',
            'processMageGetModel',
            'processMageGetSingleton',
            'processGetResourceModel',
            'processGetResourceSingleton',
            'processNew',
            //'processGetResourceHelper', //TODO: how to handle getResourceHelper?
        ];

        $index = 0;
        $length = count($tokens);

        while ($index < $length) {
            if (!is_array($tokens[$index])) {
                $index++;
                continue;
            }

            foreach ($processors as $processor) {
                $newIndex = $this->$processor($tokens, $index, $referenceClasses);
                if ($newIndex > 0) {
                    $index = $newIndex;
                    break;
                }
            }
            $index++;
        }

        foreach ($referenceClasses as &$referenceType) {
            $referenceType = array_unique($referenceType);
        }
        return $referenceClasses;
    }

    /**
     * Process class inheritance, return token index that is past the extends keyword, -1 if not a match
     *
     * @param array $tokens
     * @param int $index
     * @param array $referenceClasses
     * @return int
     */
    protected function processExtends(array &$tokens, $index, array &$referenceClasses)
    {
        if (!is_array($tokens[$index]) || $tokens[$index][0] != T_EXTENDS) {
            return -1;
        }

        $increment = 1;
        while (!is_array($tokens[$index + $increment]) || $tokens[$index + $increment][0] != T_STRING) {
            $increment++;
        }
        $className = $tokens[$index + $increment][1];

        $referenceClasses[$className][] = self::CONTEXT_EXTENDS;

        return $index + $increment;
    }

    /**
     * Process implments keyword, return index after the keyword or -1 if not a match
     *
     * @param array $tokens
     * @param int $index
     * @param array $referenceClasses
     * @return int
     */
    protected function processImplements(array &$tokens, $index, array &$referenceClasses)
    {
        if (!is_array($tokens[$index]) || $tokens[$index][0] != T_IMPLEMENTS) {
            return -1;
        }

        $increment = 1;
        while ((is_array($tokens[$index + $increment]) || $tokens[$index + $increment] != '{')
            && $tokens[$index + $increment][0] != T_EXTENDS) {
            if (is_array($tokens[$index + $increment]) && $tokens[$index + $increment][0] == T_STRING) {
                $className = $tokens[$index + $increment][1];
                $referenceClasses[$className][] = self::CONTEXT_IMPLEMENTS;
            }
            $increment++;
        }
        return $index + $increment;
    }

    /**
     * Process static method call
     *
     * @param array $tokens
     * @param int $index
     * @param array $referenceClasses
     * @return int
     */
    protected function processStaticMethodCall(array &$tokens, $index, array &$referenceClasses)
    {
        if (!$this->isStaticCall($tokens, $index)) {
            return -1;
        }

        $className = $tokens[$index][1];
        $referenceClasses[$className][] = self::CONTEXT_STATIC_METHOD_CALL;
        return $index + 1;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return bool
     */
    protected function isStaticCall(array &$tokens, $index)
    {
        $token = $tokens[$index];
        if (!isset($tokens[$index + 1])) {
            return false;
        }
        $nextToken = $tokens[$index + 1];
        return (
            is_array($token) && $token[0] == T_STRING
            && ($token[1] != 'Mage' && $token[1] != 'self' && $token[1] != 'parent')
            && (is_array($nextToken)) && $nextToken[0] == T_DOUBLE_COLON
        );
    }

    /**
     * Process keyword 'new'
     *
     * @param array $tokens
     * @param int $index
     * @param array $referenceClasses
     * @return int
     */
    protected function processNew(array &$tokens, $index, array &$referenceClasses)
    {
        if (!is_array($tokens[$index]) || $tokens[$index][0] != T_NEW) {
            return -1;
        }

        $index = $this->getNextTokenIndex($tokens, $index, 0);
        if (is_array($tokens[$index]) && $tokens[$index][0] == T_STRING) {
            $className = $tokens[$index][1];
            $referenceClasses[$className][] = self::CONTEXT_NEW;
        }
        return $index + 1;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @param int $numSkips
     * @return int|null
     */
    protected function getNextTokenIndex(&$tokens, $index, $numSkips)
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
     * @param array $tokens
     * @param int $index
     * @param array $referenceClasses
     * @return int
     */
    protected function processMageHelper(array &$tokens, $index, array &$referenceClasses)
    {
        if (!$this->isMageHelperCall($tokens, $index)) {
            return -1;
        }

        $argument = $this->getMageCallFirstArgument($tokens, $index);
        if ($argument[0] != T_VARIABLE) {
            //can't handle variable
            $className = $this->getHelperClass($argument[1]);
            if ($className !== null) {
                $referenceClasses[$className][] = self::CONTEXT_HELPER;
            }
        } else {
            $this->logger->warn('variable used in Mage::helper() call at line ' . $argument[2] . ': ' . $argument[1]);
        }
        return $index + 4;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return bool
     */
    protected function isMageHelperCall(&$tokens, $index)
    {
        if (!$this->isMageCall($tokens, $index)) {
            return false;
        }

        if (!isset($tokens[$index + 2])) {
            return false;
        }
        return is_array($tokens[$index + 2])
        && $tokens[$index + 2][0] == T_STRING
        && $tokens[$index + 2][1] == 'helper';
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return bool
     */
    protected function isMageCall(&$tokens, $index)
    {
        if (!is_array($tokens[$index]) || $tokens[$index][0] != T_STRING || $tokens[$index][1] != 'Mage') {
            return false;
        }

        if (!isset($tokens[$index + 1])) {
            return false;
        }

        return is_array($tokens[$index + 1]) && $tokens[$index + 1][0] == T_DOUBLE_COLON;
    }

    /**
     * Return the first argument of a mage static call
     *
     * @param array $tokens
     * @param int $index
     * @return mixed
     */
    protected function getMageCallFirstArgument(&$tokens, $index)
    {
        //Mage::helper('core') or Mage::getModel('core', 'additionalArguments')
        $index = $this->getNextTokenIndex($tokens, $index, 3);
        return $tokens[$index];
    }

    /**
     * @param string $m1
     * @return null|string
     */
    public function getHelperClass($m1)
    {
        $m1 = trim(trim($m1, '\''), '\"');

        $parts = explode('/', $m1);
        $className = $this->aliasMapper->mapAlias($parts[0], 'helper');
        if ($className === null) {
            $this->logger->warn("Can not map alias for helper: " . $parts[0]);
            return null;
        }
        if (count($parts) == 1) {
            return $className . '_Data';
        } else {
            $part2 = str_replace(' ', '_', ucwords(implode(' ', explode('_', $parts[1]))));
            return $className . '_' . $part2;
        }
    }

    /**
     * Process Mage::getModel
     *
     * @param array $tokens
     * @param int $index
     * @param array $referenceClasses
     * @return int
     */
    protected function processMageGetModel(array &$tokens, $index, array &$referenceClasses)
    {
        return $this->processMageGetGenericModelCall(
            $tokens,
            $index,
            $referenceClasses,
            'getModel',
            self::CONTEXT_GET_MODEL
        );
    }

    /**
     * @param array $tokens
     * @param int $index
     * @param array $referenceClasses
     * @param string $methodName
     * @param string $context
     * @return int
     */
    protected function processMageGetGenericModelCall(
        array &$tokens,
        $index,
        array &$referenceClasses,
        $methodName,
        $context
    ) {
        if (!$this->isMageGetGenericCall($tokens, $index, $methodName)) {
            return -1;
        }

        $argument = $this->getMageCallFirstArgument($tokens, $index);
        if (is_array($argument) && $argument[0] != T_VARIABLE) {
            //can't handle variable
            $className = $this->getModelClass($argument[1]);
            if ($className !== null) {
                $referenceClasses[$className][] = $context;
            }
        } else {
            if (is_array($argument)) {
                $this->logger->warn('variable used in Mage::get() call at line ' . $argument[2] . ': ' . $argument[1]);
            } else {
                $this->logger->error('unexpected token in Mage::get() call: ' . $argument);
            }
        }
        return $index + 4;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @param string $methodName
     * @return bool
     */
    protected function isMageGetGenericCall(&$tokens, $index, $methodName)
    {
        if (!$this->isMageCall($tokens, $index)) {
            return false;
        }

        if (!isset($tokens[$index + 2])) {
            return false;
        }
        return is_array($tokens[$index + 2])
        && $tokens[$index + 2][0] == T_STRING
        && $tokens[$index + 2][1] == $methodName;
    }

    /**
     * @param string $m1 the argument to Mage::getModel method, can be in the format of module/modelName or class name
     * @return null|string
     */
    public function getModelClass($m1)
    {
        $m1 = trim(trim($m1, '\''), '\"');

        if (strpos($m1, '/') === false) {
            return $m1;
        }

        $parts = explode('/', $m1);
        $className = $this->aliasMapper->mapAlias($parts[0], 'model');
        if ($className === null) {
            $this->logger->warn("Can not map alias for model: " . $parts[0]);
            return null;
        }
        $part2 = str_replace(' ', '_', ucwords(implode(' ', explode('_', $parts[1]))));
        return $className . '_' . $part2;
    }

    /**
     * Process Mage::getSingleton
     *
     * @param array $tokens
     * @param int $index
     * @param array $referenceClasses
     * @return int
     */
    protected function processMageGetSingleton(array &$tokens, $index, array &$referenceClasses)
    {
        return $this->processMageGetGenericModelCall(
            $tokens,
            $index,
            $referenceClasses,
            'getSingleton',
            self::CONTEXT_GET_SINGLETON
        );
    }

    /**
     * Process Mage::getResourceSingleton
     *
     * @param array $tokens
     * @param int $index
     * @param array $referenceClasses
     * @return int
     */
    protected function processGetResourceSingleton(array &$tokens, $index, array &$referenceClasses)
    {
        return $this->processMageGetGenericResourceModelCall(
            $tokens,
            $index,
            $referenceClasses,
            'getResourceSingleton',
            self::CONTEXT_GET_RESOURCE_SINGLETON
        );
    }

    /**
     * @param array $tokens
     * @param int $index
     * @param array $referenceClasses
     * @param string $methodName
     * @param string $context
     * @return int
     */
    protected function processMageGetGenericResourceModelCall(
        array &$tokens,
        $index,
        array &$referenceClasses,
        $methodName,
        $context
    ) {
        if (!$this->isMageGetGenericCall($tokens, $index, $methodName)) {
            return -1;
        }

        $argument = $this->getMageCallFirstArgument($tokens, $index);
        if (is_array($argument) && $argument[0] != T_VARIABLE) {
            //can't handle variable
            $className = $this->getResourceModelClass($argument[1]);
            if ($className !== null) {
                $referenceClasses[$className][] = $context;
            }
        } else {
            if (is_array($argument)) {
                $this->logger->warn("The argument to Mage::getResourceModel is variable: " . $argument[1]);
            } else {
                $this->logger->error("Unexpected token in Mage::getResourceModel is variable: " . $argument);
            }
        }
        return $index + 4;
    }

    /**
     * @param string $m1
     * @return null|string
     */
    public function getResourceModelClass($m1)
    {
        $m1 = trim(trim($m1, '\''), '\"');

        if (strpos($m1, '/') === false) {
            return $m1;
        }

        $parts = explode('/', $m1);
        $className = $this->aliasMapper->mapAlias($parts[0], 'model');
        if ($className === null) {
            $this->logger->warn("Can not map alias for model: " . $parts[0]);
            return null;
        }
        $part2 = str_replace(' ', '_', ucwords(implode(' ', explode('_', $parts[1]))));
        return $className . '_Resource_' . $part2;
    }

    /**
     * Process Mage::getResourceModel
     *
     * @param array $tokens
     * @param int $index
     * @param array $referenceClasses
     * @return int
     */
    protected function processGetResourceModel(array &$tokens, $index, array &$referenceClasses)
    {
        return $this->processMageGetGenericResourceModelCall(
            $tokens,
            $index,
            $referenceClasses,
            'getResourceModel',
            self::CONTEXT_GET_RESOURCE_MODEL
        );
    }
}
