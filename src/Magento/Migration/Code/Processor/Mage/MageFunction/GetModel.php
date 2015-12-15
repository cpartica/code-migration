<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class GetModel extends AbstractFunction implements \Magento\Migration\Code\Processor\Mage\MageFunctionInterface
{
    /**
     * @var string
     */
    protected $modelFactoryClass = null;

    /**
     * @var string
     */
    protected $methodName = null;

    /**
     * @var int
     */
    protected $endIndex = null;

    /**
     * @var string
     */
    protected $diVariableName = null;

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
        $argument = $this->getMageCallFirstArgument($this->index);
        if (is_array($argument) && $argument[0] != T_VARIABLE) {
            $classAlias = trim($argument[1], '\'"');
            if ($this->isCollectionCall()) {
                $classAlias .= '_collection';
                $this->modelFactoryClass = $this->getModelFactoryClass($classAlias, 'resource_model');
                $this->removeCollectionCall();
            } else {
                $this->modelFactoryClass = $this->getModelFactoryClass($classAlias, 'model');
            }

            if (!$this->modelFactoryClass) {
                return $this;
            }
            if ($this->modelFactoryClass == "obsolete") {
                $this->logger->warn(
                    'Obsolete class not converted at ' . $this->tokens[$this->index][2] . ' ' . $classAlias
                );
                return $this;
            }
        } else {
            if (!is_array($argument)) {
                $this->logger->warn('Unexpected token for getModel call at ' . $this->tokens[$this->index][2]);
            } else {
                $this->logger->warn(
                    'Variable inside a Mage::getModel call not converted at ' . $this->tokens[$this->index][2]
                );
            }
            return $this;
        }

        $this->diVariableName = $this->generateVariableName($this->modelFactoryClass);
        $this->methodName = $this->getModelMethod();

        $this->endIndex = $this->tokenHelper->skipMethodCall($this->tokens, $this->index) - 1;
        return $this;
    }

    /**
     * @param string $m1
     * @param string $type
     * @return null|string
     */
    protected function getModelFactoryClass($m1, $type)
    {
        $m1 = trim(trim($m1, '\''), '\"');

        if (strpos($m1, '/') === false) {
            //the argument is full class name
            $m1ClassName = $m1;
        } else {
            $parts = explode('/', $m1);
            $className = $this->aliasMapper->mapAlias($parts[0], $type);
            if ($className == null) {
                $this->logger->warn('Model alias not found: ' . $parts[0]);
                return null;
            }

            $part2 = str_replace(' ', '_', ucwords(implode(' ', explode('_', $parts[1]))));
            $m1ClassName = $className . '_' . $part2;
        }

        $m2ClassName = $this->classMapper->mapM1Class($m1ClassName);
        if (!$m2ClassName) {
            $m2ClassName = '\\' . str_replace('_', '\\', $m1ClassName);
        } elseif ($m2ClassName == 'obsolete') {
            $this->logger->warn('Model is obsolete: ' . $m1ClassName);
            return null;
        }

        return $m2ClassName . 'Factory';
    }

    /**
     * @param string $helperClassName
     * @return string
     */
    protected function generateVariableName($helperClassName)
    {
        $parts = explode('\\', trim($helperClassName, '\\'));

        $parts[0] = '';
        $parts[2] = '';
        $variableNameParts = $parts;

        $variableName = lcfirst(str_replace(' ', '', ucwords(implode(' ', $variableNameParts))));
        return $variableName;
    }

    /**
     * The format is Mage::getSingleton('module/helpername')->methodName
     *
     * @return string
     */
    protected function getModelMethod()
    {
        $nextIndex = $this->tokenHelper->skipMethodCall($this->tokens, $this->index);
        $nextIndex = $this->tokenHelper->getNextIndexOfTokenType($this->tokens, $nextIndex, T_STRING);
        return $this->tokens[$nextIndex][1];

    }

    /**
     * The format is Mage::getMage('module/name')->getCollection
     *
     * @return int|false
     */
    protected function isCollectionCall()
    {
        $index = $this->tokenHelper->skipMethodCall($this->tokens, $this->index);
        if ($this->tokens[$index][0] == T_WHITESPACE) {
            $nextIndex = $this->tokenHelper->getNextTokenIndex($this->tokens, $index);
        } else {
            $nextIndex = $index;
        }
        $nextNextIndex = $this->tokenHelper->getNextTokenIndex($this->tokens, $nextIndex);
        if ($this->tokens[$nextIndex][0] == T_OBJECT_OPERATOR &&
            $this->tokens[$nextNextIndex][0] == T_STRING &&
            $this->tokens[$nextNextIndex][1] == 'getCollection'
        ) {
            return $nextIndex;
        }
        return false;
    }

    /**
     * remove ->getCollection when called with getMage
     *
     * @return string
     */
    protected function removeCollectionCall()
    {
        if ($index = $this->isCollectionCall()) {
            $nextNextIndex = $this->tokenHelper->getNextTokenIndex($this->tokens, $index);
            $this->tokens[$index] = '';
            $this->tokens[$nextNextIndex] = '';
            $indexOfMethodCall = $indexOfMethodCall = $this->tokenHelper->skipMethodCall($this->tokens, $nextNextIndex);
            if ($this->tokens[$indexOfMethodCall][0] == T_WHITESPACE) {
                $indexOfMethodCall++;
            }
            $this->tokenHelper->eraseTokens($this->tokens, $nextNextIndex, $indexOfMethodCall - 1);
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return MageFunctionInterface::MAGE_GET_MODEL;
    }

    /**
     * @inheritdoc
     */
    public function getClass()
    {
        if (!$this->parsed) {
            $this->parse();
        }

        return $this->modelFactoryClass;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
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
     * @inheritdoc
     */
    public function convertToM2()
    {
        if (!$this->parsed) {
            $this->parse();
        }

        if ($this->methodName == null || $this->modelFactoryClass == null) {
            return $this;
        }

        $indexOfMethodCall = $this->tokenHelper->skipMethodCall($this->tokens, $this->index);

        $this->tokenHelper->eraseTokens($this->tokens, $this->index, $indexOfMethodCall - 1);

        //TODO: handle parameter to getModel
        $this->tokens[$this->index] = '$this->' . $this->diVariableName . '->create()';

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiVariableName()
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return $this->diVariableName;
    }

    /**
     * @inheritdoc
     */
    public function getDiClass()
    {
        return $this->getClass();
    }
}
