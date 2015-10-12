<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Migration\Code\Converter
     */
    protected $obj;

    /**
     * @var \Magento\Migration\Code\ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorMock;

    /**
     * @var \Magento\Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    public function setUp()
    {
        $this->processorMock = $this->getMock('Magento\Migration\Code\ProcessorInterface');
        $this->loggerMock = $this->getMock('\Magento\Migration\Logger\Logger');

        $tokenHelper = $this->setupTokenHelper($this->loggerMock);

        $this->obj = new \Magento\Migration\Code\Converter(
            [$this->processorMock],
            $tokenHelper,
            $this->loggerMock
        );
    }

    public function testConvert()
    {
        $content = file_get_contents(__DIR__ . '/_files/test.php');

        $this->processorMock->expects($this->once())
            ->method('process')
            ->willReturnArgument(0);

        $converted = $this->obj->convert($content);

        $this->assertEquals($content, $converted);
    }

    /**
     * @param \Magento\Migration\Logger\Logger $loggerMock
     * @return \Magento\Migration\Code\Processor\TokenHelper
     */
    public function setupTokenHelper(\Magento\Migration\Logger\Logger $loggerMock)
    {
        $argumentFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory'
        )->setMethods(['create'])
            ->getMock();
        $argumentFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\Mage\MageFunction\Argument();
                }
            );
        $tokenFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\TokenArgumentFactory'
        )->setMethods(['create'])
            ->getMock();
        $tokenFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\TokenArgument();
                }
            );


        $tokenCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\TokenArgumentCollectionFactory'
        )->setMethods(['create'])
            ->getMock();
        $tokenCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\TokenArgumentCollection();
                }
            );


        $callCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\CallArgumentCollectionFactory'
        )->setMethods(['create'])
            ->getMock();
        $callCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\CallArgumentCollection();
                }
            );

        $tokenHelper = new \Magento\Migration\Code\Processor\TokenHelper(
            $loggerMock,
            $argumentFactoryMock,
            $tokenFactoryMock,
            $tokenCollectionFactoryMock,
            $callCollectionFactoryMock
        );

        return $tokenHelper;
    }
}
