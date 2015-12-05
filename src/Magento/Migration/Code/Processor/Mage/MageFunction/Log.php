<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class Log extends AbstractFunction implements \Magento\Migration\Code\Processor\Mage\MageFunctionInterface
{
    /**
     * @var string
     */
    protected $diClass = '\Psr\Log\LoggerInterface';

    /**
     * @var string
     */
    protected $methodName = 'log';

    /**
     * @var int
     */
    protected $endIndex = null;

    /**
     * @var string
     */
    protected $diVariableName = 'logger';

    /**
     * @var array
     */
    protected $errorLevelMap = [
        \Zend_Log::EMERG    => '\\Monolog\\Logger::EMERGENCY',
        \Zend_Log::ALERT    => '\\Monolog\\Logger::ALERT',
        \Zend_Log::CRIT     => '\\Monolog\\Logger::CRITICAL',
        \Zend_Log::ERR      => '\\Monolog\\Logger::ERROR',
        \Zend_Log::WARN     => '\\Monolog\\Logger::WARNING',
        \Zend_Log::NOTICE   => '\\Monolog\\Logger::NOTICE',
        \Zend_Log::INFO     => '\\Monolog\\Logger::INFO',
        \Zend_Log::DEBUG    => '\\Monolog\\Logger::DEBUG',
    ];

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return MageFunctionInterface::MAGE_LOG;
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

        $arguments = $this->tokenHelper->getCallArguments($this->tokens, $this->index);
        $count = $arguments->getCount();
        if ($count == 1) {
            //Mage::log($message)
            $this->tokens[$this->index] = '$this->' . $this->diVariableName . '->debug';
        } else if ($count > 1) {
            //Mage::log($message, Zend_Log::INFO)
            //Mage::log($message, Zend_Log::INFO, 'custom.log')
            //Mage::log($message, Zend_Log::INFO, 'custom.log', true)
            $this->tokens[$this->index] = '$this->' . $this->diVariableName . '->log';
            $errorLevel = $this->convertErrorLevel($arguments->getArgument(2)->getString());
            $argumentsNew = $errorLevel . ', ' . $arguments->getArgument(1)->getString();
            $this->tokenHelper->replaceCallArgumentsTokens($this->tokens, $this->index, $argumentsNew);
            if ($count > 2) {
                $this->logger->warn('Discarded obsolete arguments of Mage::log()');
            }
        }

        return $this;
    }

    /**
     * Convert error level from Zend to PSR format, used in M1 and M2 respectively
     *
     * @param mixed $level
     * @return mixed
     */
    protected function convertErrorLevel($level)
    {
        $levelValue = defined($level) ? constant($level) : $level;
        if (array_key_exists($levelValue, $this->errorLevelMap)) {
            return $this->errorLevelMap[$levelValue];
        }
        return $level;
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
