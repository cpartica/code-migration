<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Table;

use Magento\Migration\Code\Processor\CallArgumentCollection;
use Magento\Migration\Code\Processor\TokenArgumentCollection;

class TableFunctionMatcher implements \Magento\Migration\Code\Processor\Mage\MatcherInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->tokenHelper = $tokenHelper;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return \Magento\Migration\Code\Processor\Table\TableFunctionInterface|null
     */
    public function match(&$tokens, $index)
    {
        if (!$this->isTableCall($tokens, $index)) {
            return null;
        } else {
            // @var \Magento\Migration\Code\Processor\Table\TableFunction\Table $tableFunction
            $tableFunction = $this->objectManager->create(
                'Magento\Migration\Code\Processor\Table\TableFunction\Table'
            );
            $tableFunction->setContext($tokens, $index);
            return $tableFunction;
        }
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function isTableCall(&$tokens, $index)
    {
        for ($i = $index; $i <= $index + 2; $i++) {
            if (!is_array($tokens[$i])) {
                return false;
            }
        }

        if ($tokens[$index + 1][0] != T_OBJECT_OPERATOR ||
            (
                $tokens[$index + 2][1] != 'getTable' &&
                $tokens[$index + 2][1] != 'getTableName' &&
                $tokens[$index + 2][1] != 'getIdxName' &&
                $tokens[$index + 2][1] != '_init'
            )
        ) {
            return false;
        }

        /** @var CallArgumentCollection $arguments */
        $arguments = $this->tokenHelper->getCallArguments($tokens, $index + 2);
        if (!$arguments->getCount() > 1) {
            return false;
        }

        if (!$arguments->getFirstArgument()) {
            return false;
        }

        /** @var \Magento\Migration\Code\Processor\TokenArgument $token */
        $token = $arguments->getFirstArgument()->getFirstToken();
        if ($token === null) {
            //e.g., getTable()
            return false;
        }

        if ($token->getType() == T_ARRAY || $token->getType() == T_CONSTANT_ENCAPSED_STRING) {
            if ($token->getType() == T_CONSTANT_ENCAPSED_STRING) {
                if (!preg_match('/^.+\/.+$/', $token->getName())) {
                    return false;
                }
            } elseif ($token->getType() == T_ARRAY) {
                /** @var CallArgumentCollection $arrayArguments */
                $arrayArguments = $this->tokenHelper
                    ->getCallArguments($tokens, $token = $arguments->getFirstArgument()->getTokenIndex(1));
                /** @var \Magento\Migration\Code\Processor\TokenArgument $tokenInArray */
                $tokenInArray = $arrayArguments->getFirstArgument()->getFirstToken();
                if (!preg_match('/^.+\/.+$/', $tokenInArray->getName())) {
                    return false;
                }
            }
        } else {
            $this->logger->warn('getTable array argument not using string call: ' . $tokens[$index + 4][2]);
            return false;
        }

        return true;
    }
}
