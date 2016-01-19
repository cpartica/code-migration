<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;
use Magento\Migration\Mapping\Alias;

class GetSingleton extends AbstractFunction implements \Magento\Migration\Code\Processor\Mage\MageFunctionInterface
{
    /**
     * @var \Magento\Migration\Code\Processor\NamingHelper
     */
    protected $namingHelper;

    /**
     * @var string
     */
    protected $classAliasType;

    /**
     * @var string
     */
    protected $singletonClass = null;

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
     * @param string $classAliasType
     */
    public function __construct(
        \Magento\Migration\Mapping\ClassMapping $classMapper,
        \Magento\Migration\Mapping\Alias $aliasMapper,
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory $argumentFactory,
        \Magento\Migration\Code\Processor\NamingHelper $namingHelper,
        $classAliasType = Alias::TYPE_MODEL
    ) {
        parent::__construct($classMapper, $aliasMapper, $logger, $tokenHelper, $argumentFactory);
        $this->namingHelper = $namingHelper;
        $this->classAliasType = $classAliasType;
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
            $this->singletonClass = $this->getSingletonClass($classAlias);
            if (!$this->singletonClass) {
                return $this;
            }
        } else {
            if (!is_array($argument)) {
                $this->logger->warn('Unexpected token for getSingleton call at ' . $this->tokens[$this->index][2]);
            } else {
                $this->logger->warn(
                    'Variable inside a Mage::getSingleton call not converted: ' . $this->tokens[$this->index][2]
                );
            }
            return $this;
        }

        $this->diVariableName = $this->namingHelper->generateVariableName($this->singletonClass);
        $this->methodName = $this->getSingletonMethod();

        $this->endIndex = $this->tokenHelper->skipMethodCall($this->tokens, $this->index) - 1;
        return $this;
    }

    /**
     * @param string $m1ClassAlias
     * @return null|string
     */
    protected function getSingletonClass($m1ClassAlias)
    {
        $m1ClassName = $this->namingHelper->getM1ClassName($m1ClassAlias, $this->classAliasType);
        $m2ClassName = $this->namingHelper->getM2ClassName($m1ClassName);
        return $m2ClassName;
    }

    /**
     * The format is Mage::getSingleton('module/helpername')->methodName
     *
     * @return string
     */
    protected function getSingletonMethod()
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
        return MageFunctionInterface::MAGE_GET_SINGLETON;
    }

    /**
     * @inheritdoc
     */
    public function getClass()
    {
        if (!$this->parsed) {
            $this->parse();
        }

        return $this->singletonClass;
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

        if ($this->methodName === null || $this->singletonClass === null) {
            return $this;
        }

        $indexOfMethodCall = $this->tokenHelper->skipMethodCall($this->tokens, $this->index);

        $this->tokenHelper->eraseTokens($this->tokens, $this->index, $indexOfMethodCall - 1);

        $this->tokens[$this->index] = '$this->' . $this->diVariableName;

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
