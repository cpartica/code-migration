<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class Helper extends AbstractFunction implements \Magento\Migration\Code\Processor\Mage\MageFunctionInterface
{
    /**
     * @var string
     */
    protected $helperClass = null;

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
            $this->helperClass = $this->getHelperClass($argument[1]);
            if (!$this->helperClass) {
                $this->logger->warn('Can not map helper class: ' . $argument[1]);
                return $this;
            }
            if ($this->helperClass == "obsolete") {
                $this->logger->warn(
                    'Obsolete helper not converted at ' . $this->tokens[$this->index][2] . ' ' . $argument[1]
                );
                return $this;
            }
        } else {
            if (!is_array($argument)) {
                $this->logger->warn('Unexpected token for helper call at ' . $this->tokens[$this->index][2]);
            } else {
                $this->logger->warn('Variable inside a Mage::helper call not converted: ' . $argument[1]);
            }
            return $this;
        }

        $this->diVariableName = $this->generateVariableName($this->helperClass);

        $this->methodName = $this->getHelperMethod();
        if ($this->methodName == '__') {
            //Mage::helper('tax')->__ the endIndex points to '->'
            $this->endIndex = $this->tokenHelper->skipMethodCall($this->tokens, $this->index);
        } else {
            $this->endIndex = $this->tokenHelper->skipMethodCall($this->tokens, $this->index) - 1;
        }
        return $this;
    }

    /**
     * @param string $m1
     * @return null|string
     */
    protected function getHelperClass($m1)
    {
        $m1 = trim(trim($m1, '\''), '\"');

        $parts = explode('/', $m1);
        $className = $this->aliasMapper->mapAlias($parts[0], 'helper');
        if ($className == null) {
            return null;
        }

        if (count($parts) == 1) {
            $m1ClassName = $className . '_Data';
        } else {
            $part2 = str_replace(' ', '_', ucwords(implode(' ', explode('_', $parts[1]))));
            $m1ClassName = $className . '_' . $part2;
        }

        $m2ClassName = $this->classMapper->mapM1Class($m1ClassName);
        if (!$m2ClassName) {
            $m2ClassName = '\\' . str_replace('_', '\\', $m1ClassName);
        }

        return $m2ClassName;
    }

    /**
     * @param string $helperClassName
     * @return string
     */
    protected function generateVariableName($helperClassName)
    {
        $parts = explode('\\', trim($helperClassName, '\\'));

        if (count($parts) == 4 && $parts[3] == 'Data') {
            $helperNameParts = [$parts[1], 'helper'];
        } else {
            $parts[0] = '';
            $parts[2] = '';
            array_push($parts, 'helper');
            $helperNameParts = $parts;
        }
        $helperName = lcfirst(str_replace(' ', '', ucwords(implode(' ', $helperNameParts))));
        return $helperName;
    }

    /**
     * The format is Mage::helper('module/helpername')->methodName
     *
     * @return string
     */
    protected function getHelperMethod()
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
        return MageFunctionInterface::MAGE_HELPER;
    }

    /**
     * @inheritdoc
     */
    public function getClass()
    {
        if (!$this->parsed) {
            $this->parse();
        }

        if ($this->methodName == '__') {
            return null;
        }
        return $this->helperClass;
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

        if ($this->methodName == null || $this->helperClass == null) {
            return $this;
        }

        if ($this->methodName != '__') {
            $indexOfMethodCall = $this->tokenHelper->skipMethodCall($this->tokens, $this->index);
        } else {
            $indexOfMethodCall = $this->tokenHelper->getNextIndexOfTokenType(
                $this->tokens,
                $this->index,
                T_OBJECT_OPERATOR
            );
        }
        $currentIndex = $this->index;

        //TODO: change the formatting text for '__' call
        while ($currentIndex < $indexOfMethodCall) {
            if (is_array($this->tokens[$currentIndex])) {
                $this->tokens[$currentIndex][1] = '';
            } else {
                $this->tokens[$currentIndex] = '';
            }
            $currentIndex++;
        }
        if ($this->methodName != '__') {
            $this->tokens[$this->index] = '$this->' . $this->diVariableName;
        } else {
            $this->tokens[$currentIndex][1] = '';
        }

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
        if ($this->methodName == '__') {
            return null;
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
