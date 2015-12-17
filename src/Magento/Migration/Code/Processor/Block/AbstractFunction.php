<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Block;

abstract class AbstractFunction implements BlockFunctionInterface
{
    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @var \Magento\Migration\Code\Processor\NamingHelper
     */
    protected $namingHelper;

    /**
     * @var array
     */
    protected $tokens;

    /**
     * @var int
     */
    protected $index;

    /**
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
     * @param \Magento\Migration\Code\Processor\NamingHelper $namingHelper
     */
    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Processor\NamingHelper $namingHelper
    ) {
        $this->logger = $logger;
        $this->tokenHelper = $tokenHelper;
        $this->namingHelper = $namingHelper;
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
}
