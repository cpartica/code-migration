<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;
use Magento\Migration\Code\TestCase;

class LogExceptionTest extends TestCase
{
    /**
     * @var LogException
     */
    protected $obj;

    /**
     * @var \Magento\Migration\Mapping\ClassMapping|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $classMapperMock;

    /**
     * @var \Magento\Migration\Mapping\Alias|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aliasMapperMock;

    /**
     * @var \Magento\Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @var \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $argumentFactoryMock;

    public function setUp()
    {
        $this->loggerMock = $this->getMock('\Magento\Migration\Logger\Logger');

        $this->classMapperMock = $this->getMockBuilder(
            '\Magento\Migration\Mapping\ClassMapping'
        )->disableOriginalConstructor()
            ->getMock();
        $this->aliasMapperMock = $this->getMockBuilder(
            '\Magento\Migration\Mapping\Alias'
        )->disableOriginalConstructor()
            ->getMock();

        $this->tokenHelper = $this->setupTokenHelper($this->loggerMock);

        $this->argumentFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory'
        )->setMethods(['create'])
            ->getMock();

        $this->obj = new LogException(
            $this->classMapperMock,
            $this->aliasMapperMock,
            $this->loggerMock,
            $this->tokenHelper,
            $this->argumentFactoryMock
        );
    }

    /**
     * @dataProvider throwExceptionDataProvider
     * @param $inputFile
     * @param $index
     * @param $attrs
     * @param $expectedFile
     */
    public function testThrowException(
        $inputFile,
        $index,
        $attrs,
        $expectedFile
    ) {
        $file = __DIR__ . '/_files/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);

        $this->obj->setContext($tokens, $index);

        $this->assertEquals($attrs['start_index'], $this->obj->getStartIndex());
        $this->assertEquals($attrs['end_index'], $this->obj->getEndIndex());
        $this->assertEquals($attrs['method'], $this->obj->getMethod());
        $this->assertEquals($attrs['type'], $this->obj->getType());
        $this->assertEquals($attrs['class'], $this->obj->getClass());
        $this->assertEquals($attrs['di_variable_name'], $this->obj->getDiVariableName());
        $this->assertEquals($attrs['di_variable_class'], $this->obj->getDiClass());

        $this->obj->convertToM2();

        $updatedContent = $this->tokenHelper->reconstructContent($tokens);

        $expectedFile = __DIR__ . '/_files/' . $expectedFile;
        $expected = file_get_contents($expectedFile);
        $this->assertEquals($expected, $updatedContent);
    }

    public function throwExceptionDataProvider()
    {
        $data = [
            'log_exception' => [
                'input' => 'log_exception',
                'index' => 29,
                'attr' => [
                    'start_index' => 29,
                    'end_index' => 31,
                    'method' => 'logException',
                    'class' => '\Psr\Log\LoggerInterface',
                    'type' => MageFunctionInterface::MAGE_LOG_EXCEPTION,
                    'di_variable_name' => 'logger',
                    'di_variable_class' => '\Psr\Log\LoggerInterface',

                ],
                'expected' => 'log_exception_expected',
            ],
        ];
        return $data;
    }
}
