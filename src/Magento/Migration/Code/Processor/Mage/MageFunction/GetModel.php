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
        //TODO: detect the usage of Mage::getModel()->getCollection();
        if (is_array($argument) && $argument[0] != T_VARIABLE) {
            $this->modelFactoryClass = $this->getModelFactoryClass($argument[1]);
            if (!$this->modelFactoryClass) {
                $this->logger->warn('Can not map model class: ' . $argument[1]);
                return $this;
            }
            if ($this->modelFactoryClass == "obsolete") {
                $this->logger->warn(
                    'Obsolete model not converted at ' . $this->tokens[$this->index][2] . ' ' . $argument[1]
                );
                return $this;
            }
        } else {
            if (!is_array($argument)) {
                $this->logger->warn('Unexpected token for getModel call at ' . $this->tokens[$this->index][2]);
            } else {
                $this->logger->warn(
                    'Variable inside a Mage::getModel call not converted: ' . $this->tokens[$this->index][2]
                );
            }
            return $this;
        }

        $this->diVariableName = $this->generateVariableName($this->modelFactoryClass);
        $this->methodName = $this->getModelMethod();

        $this->endIndex = $this->tokenHelper->getNextIndexOfSimpleToken($this->tokens, $this->index, '(');
        return $this;
    }

    /**
     * @param string $m1
     * @return null|string
     */
    protected function getModelFactoryClass($m1)
    {
        $m1 = trim(trim($m1, '\''), '\"');

        $parts = explode('/', $m1);
        $className = $this->aliasMapper->mapAlias($parts[0], 'model');
        if ($className == null) {
            return null;
        }

        if (count($parts) == 1) {
            $this->logger->warn("Unexpected argument for getModel: " . $m1);
            return null;
        } else {
            $part2 = str_replace(' ', '_', ucwords(implode(' ', explode('_', $parts[1]))));
            $m1ClassName = $className . '_' . $part2;
        }

        $m2ClassName = $this->classMapper->mapM1Class($m1ClassName);
        if (!$m2ClassName) {
            $m2ClassName = '\\' . str_replace('_', '\\', $m1ClassName);
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
        $currentIndex = $this->index;

        while ($currentIndex < $indexOfMethodCall) {
            if (is_array($this->tokens[$currentIndex])) {
                $this->tokens[$currentIndex][1] = '';
            } else {
                $this->tokens[$currentIndex] = '';
            }
            $currentIndex++;
        }
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
