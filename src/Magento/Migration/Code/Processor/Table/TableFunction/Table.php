<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Table\TableFunction;

use Magento\Migration\Code\Processor\CallArgumentCollection;
use Magento\Migration\Code\Processor\Table\TableFunctionInterface;
use Magento\Migration\Code\Processor\TokenArgumentCollection;

class Table extends AbstractFunction implements TableFunctionInterface
{
    /**
     * @var string
     */
    protected $tableName = null;

    /**
     * @var CallArgumentCollection
     */
    protected $arguments = null;

    /**
     * @var TokenArgumentCollection
     */
    protected $suffixTokens = null;

    /**
     * @var string
     */
    protected $objectName = null;

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
    protected $argumentType = null;

    /**
     * @var bool
     */
    protected $parsed = false;

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function parse()
    {
        $this->parsed = true;
        /** @var CallArgumentCollection arguments */
        $this->arguments = $this->tokenHelper->getCallArguments($this->tokens, $this->index + 2);

        /** @var \Magento\Migration\Code\Processor\TokenArgument $argument */
        $argument = $this->arguments->getFirstArgument()->getFirstToken();
        $this->methodName = $this->getMethodName();
        if (is_object($argument) && $argument->getType() != T_VARIABLE) {
            if ($argument->getType() == T_ARRAY) {
                $this->argumentType = $this::ARG_ARRAY;
                //find the table name and suffix into the array format
                /** @var CallArgumentCollection $arrayArguments */
                $arrayArguments = $this->tokenHelper->getCallArguments(
                    $this->tokens,
                    $this->arguments->getFirstArgument()->getTokenIndex(1)
                );

                $tableToken = $arrayArguments->getFirstArgument()->getFirstToken();
                if ($tableToken->getType() == T_CONSTANT_ENCAPSED_STRING) {
                    $this->suffixTokens = $arrayArguments->getArgument(2);
                }
                $this->tableName = $this->getTableName($tableToken->getName());
            } elseif ($argument->getType() == T_CONSTANT_ENCAPSED_STRING) {
                $this->argumentType = $this::ARG_STRING;
                $this->tableName = $this->getTableName($argument->getName());
            }
            if (!$this->tableName) {
                $this->logger->warn('Can not map table : ' . $argument->getName());
                return $this;
            }
            if ($this->tableName == "obsolete") {
                $this->logger->warn(
                    'Obsolete table not converted at ' . $argument->getLine()
                );
                return $this;
            }
        } else {
            if (!is_array($argument)) {
                $this->logger->warn('Unexpected token for table name call at ' . $this->tokens[$this->index][2]);
            } else {
                $this->logger->warn('Variable inside a getTable call not converted: ' . $this->tokens[$this->index][2]);
            }
            return $this;
        }

        $this->endIndex = $this->tokenHelper->getNextIndexOfSimpleToken($this->tokens, $this->index, ')');

        if ($this->argumentType == $this::ARG_ARRAY) {
            $this->endIndex = $this->tokenHelper->getNextIndexOfSimpleToken($this->tokens, $this->endIndex, ')');
        }

        $this->objectName = $this->getObjectName();
        return $this;
    }

    /**
     * The format is $this->getTable('module/entity') or $this->getTable(array('module/entity', 'int'))
     * other variations
     *
     * $this->getTable($somevar)
     * $this->getTable(array($var1, $var2))
     *
     * @param string $m1Table
     * @return null|string
     */
    protected function getTableName($m1Table)
    {
        $m1Table = trim(trim($m1Table, '\''), '\"');

        $parts = explode('/', $m1Table);
        $tableName = $this->tableNameMapper->mapTableName($parts[0], $parts[1]);
        if ($tableName === null) {
            return null;
        }

        return $tableName;
    }

    /**
     * Return the token of argument of a getTableName call
     *
     * @return array|string|void
     */
    protected function getMethodName()
    {
        $index = $this->tokenHelper->getNextTokenIndex($this->tokens, $this->index + 1);
        if ($index) {
            if (is_array($this->tokens[$index])) {
                return $this->tokens[$index][1];
            } else {
                return $index;
            }
        }
    }

    /**
     * @return string
     */
    public function getObjectName()
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return $this->tokens[$this->index][1];
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
        if ($this->tableName === null) {
            return $this;
        }

        // avoid processing init on classes that are not resources
        if ($this->getMethodName() == '_init' &&
            !preg_match('/^Mage_Core_Model_Resource/', $this->tokenHelper->getExtendsClass($this->tokens)) &&
            !preg_match('/^Mage_Core_Model_Mysql/', $this->tokenHelper->getExtendsClass($this->tokens))
        ) {
            return $this;
        }

        if ($this->argumentType == $this::ARG_STRING) {
            $this->handleStringCall();
        } elseif ($this->argumentType == $this::ARG_ARRAY) {
            $this->handleArrayCall();
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function handleStringCall()
    {
        $tokenCollection = $this->tokenCollectionFactory->create();
        //build the replacement arguments & replace old arguments
        $token = $this->tokenFactory->create()->setName('\'' . $this->tableName . '\'');
        $tokenCollection->addToken($token, 0);

        $tokenCollection = $this->handleExtraParamsCall($tokenCollection);

        $this->replaceParams($tokenCollection);
        return $this;
    }

    /**
     * @return $this
     */
    protected function handleArrayCall()
    {
        $tokenCollection = $this->tokenCollectionFactory->create();
        $tableStringIndex = $this->tokenHelper->getNextTokenIndex($this->tokens, $this->index, 5);
        if ($this->suffixTokens) {
            if ($this->suffixTokens->getFirstToken()->getType() == T_CONSTANT_ENCAPSED_STRING) {
                $token = $this->tokenFactory->create()->setName(
                    '\'' . $this->tableName . '_' .
                    str_replace('\'', '', $this->suffixTokens->getFirstToken()->getName()) . '\''
                );
                $tokenCollection->addToken($token, 0);
            } elseif ($this->suffixTokens->getFirstToken()->getType() != T_CONSTANT_ENCAPSED_STRING) {
                $token = $this->tokenFactory->create()->setName(
                    '\'' . $this->tableName . '_\' . ' . $this->suffixTokens->getString()
                );
                $tokenCollection->addToken($token, 0);
            }

            $tokenCollection = $this->handleExtraParamsCall($tokenCollection);

            $this->replaceParams($tokenCollection);
        } else {
            $this->logger->warn(
                'Expecting suffix for array table name call at ' . $this->tokens[$tableStringIndex][2]
            );
        }
        return $this;
    }

    /**
     * @param \Magento\Migration\Code\Processor\TokenArgumentCollection $tokenCollection
     * @return \Magento\Migration\Code\Processor\TokenArgumentCollection
     */
    protected function handleExtraParamsCall($tokenCollection)
    {
        //getIdxName extra params
        if ($this->arguments->getCount()>1) {
            for ($cnt = 2; $cnt <= $this->arguments->getCount(); $cnt++) {
                $token = $this->tokenFactory->create()->setName(", ");
                $tokenCollection->addToken($token, 0);
                foreach ($this->arguments->getArgument($cnt)->getTokens() as $arg) {
                    $tokenCollection->addToken($arg, 0);
                }
            }
        }
        return $tokenCollection;
    }

    /**
     * @param \Magento\Migration\Code\Processor\TokenArgumentCollection $tokenCollection
     * @return void
     */
    protected function replaceParams($tokenCollection)
    {
        $this->tokenHelper->replaceCallArgumentsTokens($this->tokens, $this->index, $tokenCollection);
    }
}
