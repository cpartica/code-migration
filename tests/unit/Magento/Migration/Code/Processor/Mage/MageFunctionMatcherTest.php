<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage;

class MageFunctionMatcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Migration\Code\Processor\Mage\MageFunctionMatcher
     */
    protected $obj;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    public function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(
            '\Magento\Framework\ObjectManagerInterface'
        )->getMock();

        $this->obj = new \Magento\Migration\Code\Processor\Mage\MageFunctionMatcher(
            $this->objectManagerMock
        );
    }

    /**
     * @param $inputFile
     * @dataProvider matchDataProvider
     */
    public function testMatch($inputFile, $expectedMatchResult)
    {
        $file = __DIR__ . '/_files/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);

        $invocationIndex = 0;
        foreach ($expectedMatchResult as $matchedFunction) {
            $mockFunctionObj = $this->getMockBuilder($matchedFunction)
                ->disableOriginalConstructor()
                ->getMock();
            $this->objectManagerMock->expects($this->at($invocationIndex++))
                ->method('create')
                ->with($matchedFunction)
                ->willReturn($mockFunctionObj);
        }
        $count = count($tokens);
        $matchResult = [];
        for ($i = 0; $i < $count - 3; $i++) {
            $matchedFunction = $this->obj->match($tokens, $i);
            if ($matchedFunction != null) {
                $matchResult[$i] = $matchedFunction;
            }
        }

        $this->assertEquals(
            array_keys($expectedMatchResult),
            array_keys($matchResult)
        );
    }

    public function matchDataProvider()
    {
        $data = [
            'mage_functions' => [
                'input' => 'mage_functions',
                'expected' => [
                    29 => '\Magento\Migration\Code\Processor\Mage\MageFunction\Helper',
                    38 => '\Magento\Migration\Code\Processor\Mage\MageFunction\Helper',
                    47 => '\Magento\Migration\Code\Processor\Mage\MageFunction\GetModel',
                    57 => '\Magento\Migration\Code\Processor\Mage\MageFunction\App',
                    70 => '\Magento\Migration\Code\Processor\Mage\MageFunction\GetStoreConfig',
                    78 => '\Magento\Migration\Code\Processor\Mage\MageFunction\Registry',
                    85 => '\Magento\Migration\Code\Processor\Mage\MageFunction\GetSingleton',
                    97 => '\Magento\Migration\Code\Processor\Mage\MageFunction\Registry',
                    105 => '\Magento\Migration\Code\Processor\Mage\MageFunction\Registry',
                ]
            ],
        ];
        return $data;
    }
}
