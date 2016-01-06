<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;
use Magento\Migration\Mapping\Alias;

class GetResourceModel extends AbstractFunction implements \Magento\Migration\Code\Processor\Mage\MageFunctionInterface
{
    /**
     * @var \Magento\Migration\Code\Processor\NamingHelper
     */
    protected $namingHelper;

    /**
     * @var string
     */
    protected $className = null;

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
     * @param \Magento\Migration\Mapping\Alias $aliasMapper
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
        if ($this->parsed) {
            return;
        }
        $this->parsed = true;

        $arguments = $this->tokenHelper->getCallArguments($this->tokens, $this->index);

        if ($arguments->getFirstArgument()) {
            if ($arguments->getFirstArgument()->getFirstToken()->getType() != T_VARIABLE) {
                $classAlias = trim($arguments->getFirstArgument()->getString(), '\'"');
                $this->className = $this->getFactoryClass($classAlias, Alias::TYPE_RESOURCE_MODEL);
                if ($this->className) {
                    $this->diVariableName = $this->namingHelper->generateVariableName($this->className);
                }
            } else {
                $this->logger->warn(sprintf(
                    'Variable inside a Mage::getResourceModel call not converted at %s',
                    $arguments->getFirstArgument()->getFirstToken()->getLine()
                ));
            }
        }

        $this->methodName = $this->getModelMethod();

        $this->endIndex = $this->tokenHelper->skipMethodCall($this->tokens, $this->index) - 1;
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
     * @return string
     */
    protected function getModelMethod()
    {
        //Mage::getResourceModel('module/class')->methodName
        $nextIndex = $this->tokenHelper->skipMethodCall($this->tokens, $this->index);
        $nextIndex = $this->tokenHelper->getNextIndexOfTokenType($this->tokens, $nextIndex, T_STRING);
        return $this->tokens[$nextIndex][1];
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return MageFunctionInterface::MAGE_GET_RESOURCE_MODEL;
    }

    /**
     * @inheritdoc
     */
    public function getClass()
    {
        $this->parse();
        return $this->className;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
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
        return ($this->tokenHelper->skipMethodCall($this->tokens, $this->index) - 1);
    }

    /**
     * @inheritdoc
     */
    public function convertToM2()
    {
        $this->parse();

        if (!$this->className) {
            return $this;
        }

        $indexOfMethodCall = $this->tokenHelper->skipMethodCall($this->tokens, $this->index);

        $this->tokenHelper->eraseTokens($this->tokens, $this->index, $indexOfMethodCall - 1);

        //TODO: handle parameter to getResourceModel
        $this->tokens[$this->index] = '$this->' . $this->diVariableName . '->create()';

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiVariableName()
    {
        $this->parse();
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
