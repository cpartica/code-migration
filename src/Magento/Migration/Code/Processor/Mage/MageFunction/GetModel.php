<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;
use Magento\Migration\Mapping\Alias;

class GetModel extends AbstractFunction implements \Magento\Migration\Code\Processor\Mage\MageFunctionInterface
{
    /**
     * @var \Magento\Migration\Code\Processor\NamingHelper
     */
    protected $namingHelper;

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
            if ($this->isCollectionCall()) {
                $classAlias .= '_collection';
                $this->modelFactoryClass = $this->getFactoryClass($classAlias, Alias::TYPE_RESOURCE_MODEL);
                $this->removeCollectionCall();
            } else {
                $this->modelFactoryClass = $this->getFactoryClass($classAlias, Alias::TYPE_MODEL);
            }
            if (!$this->modelFactoryClass) {
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

        $this->diVariableName = $this->namingHelper->generateVariableName($this->modelFactoryClass);
        $this->methodName = $this->getModelMethod();

        $this->endIndex = $this->tokenHelper->skipMethodCall($this->tokens, $this->index) - 1;
        return $this;
    }

    /**
     * @param string $m1ClassAlias
     * @param string $type
     * @return null|string
     */
    protected function getFactoryClass($m1ClassAlias, $type)
    {
        $m1ClassName = $this->namingHelper->getM1ClassName($m1ClassAlias, $type);
        $m2ClassName = $this->namingHelper->getM2FactoryClassName($m1ClassName);
        return $m2ClassName;
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

        if ($this->methodName === null || $this->modelFactoryClass === null) {
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
