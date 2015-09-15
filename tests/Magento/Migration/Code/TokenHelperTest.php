<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code;

class TokenHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $obj;

    /**
     * @var \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $argumentFactoryMock;

    /**
     * @var \Magento\Migration\Code\Processor\TokenArgumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenFactoryMock;

    /**
     * @var \Magento\Migration\Code\Processor\TokenArgumentCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenCollectionFactoryMock;

    /**
     * @var \Magento\Migration\Code\Processor\CallArgumentCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $callCollectionFactoryMock;

    /**
     * @var \Magento\Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    public function setUp()
    {
        $this->argumentFactoryMock = $this->getMock(
            '\Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory'
        );
        $this->tokenFactoryMock = $this->getMock(
            '\Magento\Migration\Code\Processor\TokenArgumentFactory'
        );
        $this->tokenCollectionFactoryMock = $this->getMock(
            '\Magento\Migration\Code\Processor\TokenArgumentCollectionFactory'
        );
        $this->callCollectionFactoryMock = $this->getMock(
            '\Magento\Migration\Code\Processor\CallArgumentCollectionFactory'
        );
        $this->loggerMock = $this->getMock('\Magento\Migration\Logger\Logger');

        $this->obj = new \Magento\Migration\Code\Processor\TokenHelper(
            $this->loggerMock,
            $this->argumentFactoryMock,
            $this->tokenFactoryMock,
            $this->tokenCollectionFactoryMock,
            $this->callCollectionFactoryMock
        );
    }

    public function testGetNextTokenIndex()
    {
        $tokens = [
            [T_STRING, 'Mage', 1],
            [T_WHITESPACE, "\n\t", 1],
            ',',
            [T_WHITESPACE, "\n\t", 2],
            [T_DOUBLE_COLON, '::', 3],
        ];

        $this->assertEquals(2, $this->obj->getNextTokenIndex($tokens, 0));
        $this->assertEquals(4, $this->obj->getNextTokenIndex($tokens, 0, 1));
        $this->assertNull($this->obj->getNextTokenIndex($tokens, 0, 2));
    }

    public function testSkipFunctionCall()
    {

    }
}
