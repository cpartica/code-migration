<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class DispatchEvent extends AbstractFunction implements \Magento\Migration\Code\Processor\Mage\MageFunctionInterface
{
    /**
     * @var \Magento\Migration\Code\Processor\ConstructorHelper
     */
    protected $constructorHelper;

    /**
     * @var string
     */
    protected $diClass = '\Magento\Framework\Event\ManagerInterface';

    /**
     * @var string
     */
    protected $methodName = 'dispatchEvent';

    /**
     * @var int
     */
    protected $endIndex = null;

    /**
     * @var string
     */
    protected $diVariableName = 'eventManager';

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return MageFunctionInterface::MAGE_DISPATCH_EVENT;
    }

    /**
     * @inheritdoc
     */
    public function getClass()
    {
        return $this->diClass;
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
        return $this->index + 2;
    }

    /**
     * @inheritdoc
     */
    public function convertToM2()
    {
        $indexOfMethodCall = $this->index + 2;

        $this->tokenHelper->eraseTokens($this->tokens, $this->index, $indexOfMethodCall);

        $this->tokens[$this->index] = '$this->' . $this->diVariableName . '->dispatch';

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiVariableName()
    {
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
