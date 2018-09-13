<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Table\TableFunction;

use Magento\Migration\Code\Processor\Table\TableFunction\Table;

/**
 * Class TableTest
 */
class TableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Migration\Code\Processor\Table\TableFunction\Table
     */
    protected $model;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tableNameMapper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenCollectionFactory;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $className = '\Magento\Migration\Mapping\TableName';
        $this->tableNameMapper = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $className = '\Magento\Migration\Logger\Logger';
        $this->logger = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $className = '\Magento\Migration\Code\Processor\TokenHelper';
        $this->tokenHelper = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $className = '\Magento\Migration\Code\Processor\TokenArgumentFactory';
        $this->tokenFactory = $this->getMockBuilder($className)
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $className = '\Magento\Migration\Code\Processor\TokenArgumentCollectionFactory';
        $this->tokenCollectionFactory = $this->getMockBuilder($className)
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $this->model = $this->objectManager->getObject(
            'Magento\Migration\Code\Processor\Table\TableFunction\Table',
            [
                'tableNameMapper' => $this->tableNameMapper,
                'logger' => $this->logger,
                'tokenHelper' => $this->tokenHelper,
                'tokenFactory' => $this->tokenFactory,
                'tokenCollectionFactory' => $this->tokenCollectionFactory,
            ]
        );
    }


    /**
     * test ConvertToM2 (also tests getObjectName, getEndIndex, getStartIndex)
     * @param mixed[] $tokens
     * @param string $index
     * @dataProvider setContextProvider
     */
    public function testConvertToM2($tokens, $index)
    {
        $this->model->setContext($tokens, $index);

        //parse
        $className = '\Magento\Migration\Code\Processor\CallArgumentCollection';
        $callArgCollection = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $className = '\Magento\Migration\Code\Processor\TokenArgument';
        $tokenArgument = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $tokenArgument->expects($this->atLeastOnce())
            ->method('getType')
            ->willReturn(T_CONSTANT_ENCAPSED_STRING);

        $tokenArgument->expects($this->once())
            ->method('getName')
            ->willReturn('catalogrule/rule_product');

        $className = '\Magento\Migration\Code\Processor\TokenArgumentCollection';
        $tokenArgCollection = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $tokenArgCollection->expects($this->once())
            ->method('getFirstToken')
            ->willReturn($tokenArgument);

        $callArgCollection->expects($this->once())
            ->method('getFirstArgument')
            ->willReturn($tokenArgCollection);

        $this->tokenHelper->expects($this->any())
            ->method('getCallArguments')
            ->willReturn($callArgCollection);

        $this->tableNameMapper->expects($this->any())
            ->method('mapTableName')
            ->willReturn('catalogrule_rule_product');

        $this->tokenHelper->expects($this->any())
            ->method('getNextIndexOfSimpleToken')
            ->willReturn(8);

        //convert
        $className = '\Magento\Migration\Code\Processor\TokenArgument';
        $token = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $token->expects($this->once())
            ->method('setName')
            ->willReturnSelf();

        $this->tokenFactory->expects($this->any())
            ->method('create')
            ->willReturn($token);

        $className = '\Magento\Migration\Code\Processor\TokenArgumentCollection';
        $collection = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $collection->expects($this->once())
            ->method('addToken')
            ->with($token, 0);

        $this->tokenHelper->expects($this->once())
            ->method('replaceCallArgumentsTokens')
            ->with($tokens, $index, $collection);

        $this->tokenCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($collection);

        $this->model->convertToM2();

        $this->assertEquals('$this', $this->model->getObjectName());
        $this->assertEquals(8, $this->model->getEndIndex());
        $this->assertEquals($index, $this->model->getStartIndex());
    }

    /**
     * test SetContext
     * @param mixed[] $tokens
     * @param string $index
     * @dataProvider setContextProvider
     */
    public function testSetContext($tokens, $index)
    {
        $this->assertNull($this->model->setContext($tokens, $index));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setContextProvider()
    {
        return [
            [
                'tokens' => [
                    0 =>
                        [
                            0 => T_WHITESPACE,
                            1 => ' ',
                            2 => 101,
                            3 => 'T_WHITESPACE',
                        ],
                    1 =>
                        [
                            0 => T_DOUBLE_ARROW,
                            1 => '=>',
                            2 => 101,
                            3 => 'T_DOUBLE_ARROW',
                        ],
                    2 =>
                        [
                            0 => T_WHITESPACE,
                            1 => ' ',
                            2 => 101,
                            3 => 'T_WHITESPACE',
                        ],
                    3 =>
                        [
                            0 => T_VARIABLE,
                            1 => '$this',
                            2 => 101,
                            3 => 'T_VARIABLE',
                        ],
                    4 =>
                        [
                            0 => T_OBJECT_OPERATOR,
                            1 => '->',
                            2 => 101,
                            3 => 'T_OBJECT_OPERATOR',
                        ],
                    5 =>
                        [
                            0 => T_STRING,
                            1 => 'getTable',
                            2 => 101,
                            3 => 'T_STRING',
                        ],
                    6 => '(',
                    7 =>
                        [
                            0 => T_CONSTANT_ENCAPSED_STRING,
                            1 => '\'catalogrule/rule_product\'',
                            2 => 101,
                            3 => 'T_CONSTANT_ENCAPSED_STRING',
                        ],
                    8 => ')',
                    9 => ')',
                    10 => ')',
                ],
                'index' => '3',
            ]
        ];
    }
}
