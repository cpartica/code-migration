<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;
use Magento\Migration\Mapping\Alias;

class Helper extends AbstractFunction implements \Magento\Migration\Code\Processor\Mage\MageFunctionInterface
{
    /**
     * @var \Magento\Migration\Code\Processor\NamingHelper
     */
    protected $namingHelper;

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
     * @param \Magento\Migration\Mapping\ClassMapping $classMapper
     * @param Alias $aliasMapper
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
     * @param ArgumentFactory $argumentFactory
     * @param \Magento\Migration\Code\Processor\NamingHelper $namingHelper
     */
    public function __construct(
        \Magento\Migration\Mapping\ClassMapping $classMapper,
        \Magento\Migration\Mapping\Alias $aliasMapper,
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory $argumentFactory,
        \Magento\Migration\Code\Processor\NamingHelper $namingHelper
    ) {
        parent::__construct($classMapper, $aliasMapper, $logger, $tokenHelper, $argumentFactory);
        $this->namingHelper = $namingHelper;
    }

    /**
     * @return $this
     */
    private function parse()
    {
        $this->parsed = true;
        $argument = $this->getMageCallFirstArgument($this->index);
        if (is_array($argument) && $argument[0] != T_VARIABLE) {
            $classAlias = trim($argument[1], '\'"');
            $classAlias .= ($classAlias && strpos($classAlias, '/') === false) ? '/data' : '';
            $this->helperClass = $this->getHelperClass($classAlias);
            if (!$this->helperClass) {
                $this->logger->warn('Can not map helper class: ' . $argument[1]);
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
     * @param string $m1ClassAlias
     * @return null|string
     */
    protected function getHelperClass($m1ClassAlias)
    {
        $m1ClassName = $this->namingHelper->getM1ClassName($m1ClassAlias, Alias::TYPE_HELPER);
        $m2ClassName = $this->namingHelper->getM2ClassName($m1ClassName);
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

        if ($this->methodName === null || $this->helperClass === null) {
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

        $this->tokenHelper->eraseTokens($this->tokens, $this->index, $indexOfMethodCall - 1);

        //TODO: change the formatting text for '__' call
        if ($this->methodName != '__') {
            $this->tokens[$this->index] = '$this->' . $this->diVariableName;
        } else {
            $this->tokens[$indexOfMethodCall][1] = '';
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
