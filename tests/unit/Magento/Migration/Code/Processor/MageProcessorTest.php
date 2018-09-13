<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

use Magento\Migration\Code\TestCase;

class MageProcessorTest extends TestCase
{
    /**
     * @var MageProcessor
     */
    protected $obj;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Migration\Code\Processor\Mage\MageFunctionMatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $matcherMock;

    /**
     * @var \Magento\Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Migration\Code\Processor\DiVariablesPersistent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $diVariablesPersistentMock;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    public function setUp()
    {
        $this->loggerMock = $this->getMockBuilder('\Magento\Migration\Logger\Logger')
            ->disableOriginalConstructor()->getMock();
        $this->diVariablesPersistentMock = $this
            ->getMockBuilder('\Magento\Migration\Code\Processor\DiVariablesPersistent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(
            '\Magento\Framework\ObjectManagerInterface'
        )->getMock();
        $this->matcherMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunctionMatcher'
        )->disableOriginalConstructor()
            ->getMock();

        $this->tokenHelper = $this->setupTokenHelper($this->loggerMock);

        $this->obj = new MageProcessor(
            $this->objectManagerMock,
            $this->diVariablesPersistentMock,
            $this->tokenHelper,
            $this->matcherMock
        );
    }

    /**
     * @dataProvider processNoClassDataProvider
     * @param string $inputFile
     * @param string $expectedFile
     */
    public function testProcessNoClass($inputFile, $expectedFile)
    {
        $file = __DIR__ . '/_files/mage_processor/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);

        $this->matcherMock->expects($this->never())
            ->method('match');

        $processedTokens = $this->obj->process($tokens);

        $updatedContent = $this->tokenHelper->reconstructContent($processedTokens);

        $expectedFile = __DIR__ . '/_files/mage_processor/' . $expectedFile;
        $expected = file_get_contents($expectedFile);
        $this->assertEquals($expected, $updatedContent);
    }

    public function processNoClassDataProvider()
    {
        $data = [
            'no_class' => [
                'input' => 'no_class',
                'expected' => 'no_class_expected'
            ],
        ];
        return $data;
    }

    /**
     * @dataProvider processDataProvider
     * @param string $inputFile
     * @param string $expectedFile
     */
    public function testProcess($inputFile, $expectedFile)
    {
        $file = __DIR__ . '/_files/mage_processor/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);

        $helperName = 'taxHelper';
        $helperClass = '\Magento\Tax\Helper\Data';
        $modelName = 'catalogCategory';
        $modelClass = '\Magento\Catalog\Model\Category';


        $helperMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunction\Helper'
        )->disableOriginalConstructor()
            ->getMock();
        $helperMock->expects($this->once())
            ->method('convertToM2');
        $helperMock->expects($this->atLeastOnce())
            ->method('getDiVariableName')
            ->willReturn($helperName);
        $helperMock->expects($this->once())
            ->method('getClass')
            ->willReturn($helperClass);

        $getModelMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunction\GetModel'
        )->disableOriginalConstructor()
            ->getMock();
        $getModelMock->expects($this->once())
            ->method('convertToM2');
        $getModelMock->expects($this->atLeastOnce())
            ->method('getDiVariableName')
            ->willReturn($modelName);
        $getModelMock->expects($this->once())
            ->method('getClass')
            ->willReturn($modelClass);

        $constructorMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\ConstructorHelper'
        )->disableOriginalConstructor()
            ->getMock();

        $constructorMock->expects($this->once())
            ->method('setContext')
            ->with($this->anything());
        $constructorMock->expects($this->once())
            ->method('injectArguments')
            ->with();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('\Magento\Migration\Code\Processor\ConstructorHelper', [])
            ->willReturn($constructorMock);

        $valueMap = [
            [$tokens, 29, $helperMock],
            [$tokens, 42, $getModelMock],
        ];
        $this->matcherMock->expects($this->any())
            ->method('match')
            ->willReturnMap($valueMap);

        $processedTokens = $this->obj->process($tokens);

        $updatedContent = $this->tokenHelper->reconstructContent($processedTokens);

        $expectedFile = __DIR__ . '/_files/mage_processor/' . $expectedFile;
        $expected = file_get_contents($expectedFile);
        $this->assertEquals($expected, $updatedContent);
    }

    public function processDataProvider()
    {
        $data = [
            'helper_and_getmodel' => [
                'input' => 'helper_and_getmodel',
                'expected' => 'helper_and_getmodel_expected'
            ],
        ];
        return $data;
    }
}
