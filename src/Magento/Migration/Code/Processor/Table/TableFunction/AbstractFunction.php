<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Table\TableFunction;

/**
 * Class AbstractFunction
 * Base class for
 * @package Magento\Migration\Code\Processor\Table\TableFunction
 */
class AbstractFunction
{
    /**
     * @var \Magento\Migration\Mapping\TableName
     */
    protected $tableNameMapper;

    /**
     * @var array
     */
    protected $tokens;

    /**
     * @var int
     */
    protected $index;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @var \Magento\Migration\Code\Processor\TokenArgumentCollectionFactory
     */
    protected $tokenCollectionFactory;

    /**
     * @var \Magento\Migration\Code\Processor\TokenArgumentFactory
     */
    protected $tokenFactory;

    /**
     * @param \Magento\Migration\Mapping\TableName $tableNameMapper
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
     * @param \Magento\Migration\Code\Processor\TokenArgumentFactory $tokenFactory
     * @param \Magento\Migration\Code\Processor\TokenArgumentCollectionFactory $tokenCollectionFactory
     */
    public function __construct(
        \Magento\Migration\Mapping\TableName $tableNameMapper,
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Processor\TokenArgumentFactory $tokenFactory,
        \Magento\Migration\Code\Processor\TokenArgumentCollectionFactory $tokenCollectionFactory
    ) {
        $this->tableNameMapper = $tableNameMapper;
        $this->logger = $logger;
        $this->tokenHelper = $tokenHelper;
        $this->tokenFactory = $tokenFactory;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return void
     */
    public function setContext(array &$tokens, $index = 0)
    {
        $this->tokens = &$tokens;
        $this->index = $index;
    }

    /**
     * Return the token of argument of a getTableName call
     *
     * @param int $index
     * @return array|string
     */
    protected function getTableCallFirstArgument($index)
    {
        $index = $this->tokenHelper->getNextTokenIndex($this->tokens, $index, 3);
        return $this->tokens[$index];
    }
}
