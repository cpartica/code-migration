<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;
use Magento\Migration\Code\Processor\NamingHelper;
use Magento\Migration\Mapping\Alias;

class HelperTest extends AbstractMageFunctionTestCase
{
    /**
     * @var Helper
     */
    protected $obj;

    /**
     * @var NamingHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $namingHelperMock;

    /**
     * @return GetStoreConfig
     */
    protected function getSubjectUnderTest()
    {
        $this->namingHelperMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\NamingHelper'
        )->disableOriginalConstructor()
            ->getMock();

        $this->namingHelperMock
            ->expects($this->any())
            ->method('getM1ClassName')
            ->willReturnMap([
                ['tax', Alias::TYPE_HELPER, 'Mage_Tax_Helper_Data'],
                ['tax/data', Alias::TYPE_HELPER, 'Mage_Tax_Helper_Data'],
                ['tax/config', Alias::TYPE_HELPER, 'Mage_Tax_Helper_Config'],
            ]);
        $this->namingHelperMock
            ->expects($this->any())
            ->method('getM2ClassName')
            ->willReturnMap([
                ['Mage_Tax_Helper_Data', '\\Magento\\Tax\\Helper\\Data'],
                ['Mage_Tax_Helper_Config', '\\Magento\\Tax\\Helper\\Config'],
            ]);

        return new Helper(
            $this->classMapperMock,
            $this->aliasMapperMock,
            $this->loggerMock,
            $this->tokenHelper,
            $this->argumentFactoryMock,
            $this->namingHelperMock
        );
    }

    /**
     * @dataProvider helperDataProvider
     * @param $inputFile
     * @param $index
     * @param $attrs
     * @param $mappedHelperClass
     * @param $expectedFile
     */
    public function testHelper(
        $inputFile,
        $index,
        $attrs,
        $mappedHelperClass,
        $expectedFile
    ) {
        $this->aliasMapperMock->expects($this->any())
            ->method('mapAlias')
            ->with('tax', 'helper')
            ->willReturn('Mage_Tax_Helper');

        $this->classMapperMock->expects($this->any())
            ->method('mapM1Class')
            ->willReturn($mappedHelperClass);

        $this->executeTestAgainstExpectedFile($inputFile, $index, $attrs, $expectedFile);
    }

    public function helperDataProvider()
    {
        $data = [
            'helper_translation' => [
                'input' => 'helper_translation',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 37,
                    'method' => '__',
                    'class' => null,
                    'type' => MageFunctionInterface::MAGE_HELPER,
                    'di_variable_name' => null,
                    'di_variable_class' => null,

                ],
                'mapped_helper_class' => '\Magento\Tax\Helper\Data',
                'expected' => 'helper_translation_expected',
            ],
            'helper_not_translation' => [
                'input' => 'helper_not_translation',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 36,
                    'method' => 'doSomething',
                    'class' => '\Magento\Tax\Helper\Data',
                    'type' => MageFunctionInterface::MAGE_HELPER,
                    'di_variable_name' => 'taxHelper',
                    'di_variable_class' => '\Magento\Tax\Helper\Data',

                ],
                'mapped_helper_class' => '\Magento\Tax\Helper\Data',
                'expected' => 'helper_not_translation_expected',
            ],
            'helper_variable' => [
                'input' => 'helper_variable',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => null,
                    'method' => null,
                    'class' => null,
                    'type' => MageFunctionInterface::MAGE_HELPER,
                    'di_variable_name' => null,
                    'di_variable_class' => null,

                ],
                'mapped_helper_class' => null,
                'expected' => 'helper_variable_expected',
            ],
            'helper_specific_helper' => [
                'input' => 'helper_specific_helper',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 36,
                    'method' => 'doSomething',
                    'class' => '\Magento\Tax\Helper\Config',
                    'type' => MageFunctionInterface::MAGE_HELPER,
                    'di_variable_name' => 'taxConfigHelper',
                    'di_variable_class' => '\Magento\Tax\Helper\Config',

                ],
                'mapped_helper_class' => '\Magento\Tax\Helper\Config',
                'expected' => 'helper_specific_helper_expected',
            ],
            'helper_not_mapped' => [
                'input' => 'helper_not_mapped',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 36,
                    'method' => 'doSomething',
                    'class' => '\Magento\Tax\Helper\Config',
                    'type' => MageFunctionInterface::MAGE_HELPER,
                    'di_variable_name' => 'taxConfigHelper',
                    'di_variable_class' => '\Magento\Tax\Helper\Config',

                ],
                'mapped_helper_class' => null,
                'expected' => 'helper_not_mapped_expected',
            ],
        ];
        return $data;
    }
}
