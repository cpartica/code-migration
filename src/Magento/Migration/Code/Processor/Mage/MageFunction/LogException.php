<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class LogException extends AbstractFunction implements \Magento\Migration\Code\Processor\Mage\MageFunctionInterface
{
    /**
     * @var string
     */
    protected $diClass = '\Psr\Log\LoggerInterface';

    /**
     * @var string
     */
    protected $methodName = 'logException';

    /**
     * @var int
     */
    protected $endIndex = null;

    /**
     * @var string
     */
    protected $diVariableName = 'logger';

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return MageFunctionInterface::MAGE_LOG_EXCEPTION;
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

        $this->tokens[$this->index] = '$this->' . $this->diVariableName . '->critical';

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
