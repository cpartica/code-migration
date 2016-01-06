<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;
use Magento\Migration\Mapping\Alias;

class GetSingletonTest extends AbstractMageFunctionTestCase
{
    /**
     * @var GetSingleton
     */
    protected $obj;

    /**
     * @var \Magento\Migration\Code\Processor\NamingHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $namingHelperMock;

    /**
     * @return GetSingleton
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
                ['tax/config_method', Alias::TYPE_MODEL, 'Mage_Tax_Model_Config_Method'],
                ['Mage_Tax_Model_Rule', Alias::TYPE_MODEL, 'Mage_Tax_Model_Rule'],
            ]);
        $this->namingHelperMock
            ->expects($this->any())
            ->method('getM2ClassName')
            ->willReturnMap([
                ['Mage_Tax_Model_Config_Method', '\\Magento\\Tax\\Model\\Config\\Method'],
                ['Mage_Tax_Model_Rule', '\\Magento\\Tax\\Model\\Rule'],
            ]);
        $this->namingHelperMock
            ->expects($this->any())
            ->method('generateVariableName')
            ->willReturnMap([
                ['\\Magento\\Tax\\Model\\Config\\Method', 'taxConfigMethod'],
                ['\\Magento\\Tax\\Model\\Rule', 'taxRule'],
            ]);

        return new GetSingleton(
            $this->classMapperMock,
            $this->aliasMapperMock,
            $this->loggerMock,
            $this->tokenHelper,
            $this->argumentFactoryMock,
            $this->namingHelperMock
        );
    }

    /**
     * Execute test suite on the test subject using data provider
     *
     * @dataProvider dataProvider
     *
     * @param $inputFile
     * @param $index
     * @param $attrs
     * @param $m1ClassName
     * @param $mappedModelClass
     * @param $expectedFile
     */
    public function testGetSingleton(
        $inputFile,
        $index,
        $attrs,
        $m1ClassName,
        $mappedModelClass,
        $expectedFile
    )
    {
        $this->aliasMapperMock->expects($this->any())
            ->method('mapAlias')
            ->with('tax', 'model')
            ->willReturn('Mage_Tax_Model');

        $this->classMapperMock->expects($this->any())
            ->method('mapM1Class')
            ->with($m1ClassName)
            ->willReturn($mappedModelClass);

        $this->executeTestAgainstExpectedFile($inputFile, $index, $attrs, $expectedFile);
    }

    public function dataProvider()
    {
        $data = [
            'singleton_mapped' => [
                'input' => 'model_mapped',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 36,
                    'method' => 'doSomething',
                    'class' => '\Magento\Tax\Model\Config\Method',
                    'type' => MageFunctionInterface::MAGE_GET_SINGLETON,
                    'di_variable_name' => 'taxConfigMethod',
                    'di_variable_class' => '\Magento\Tax\Model\Config\Method',

                ],
                'm1_class_name' => 'Mage_Tax_Model_Config_Method',
                'mapped_singleton_class_name' => '\Magento\Tax\Model\Config\Method',
                'expected' => 'singleton_mapped_expected',
            ],
            'singleton_not_mapped' => [
                'input' => 'singleton_not_mapped',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 36,
                    'method' => 'doSomething',
                    'class' => '\Magento\Tax\Model\Config\Method',
                    'type' => MageFunctionInterface::MAGE_GET_SINGLETON,
                    'di_variable_name' => 'taxConfigMethod',
                    'di_variable_class' => '\Magento\Tax\Model\Config\Method',

                ],
                'm1_class_name' => 'Mage_Tax_Model_Config_Method',
                'mapped_singleton_class_name' => null,
                'expected' => 'singleton_not_mapped_expected',
            ],
            'singleton_direct_model_name' => [
                'input' => 'singleton_direct_model_name',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 36,
                    'method' => 'doSomething',
                    'class' => '\Magento\Tax\Model\Rule',
                    'type' => MageFunctionInterface::MAGE_GET_SINGLETON,
                    'di_variable_name' => 'taxRule',
                    'di_variable_class' => '\Magento\Tax\Model\Rule',

                ],
                'm1_class_name' => 'Mage_Tax_Model_Rule',
                'mapped_singleton_class_name' => '\Magento\Tax\Model\Rule',
                'expected' => 'singleton_direct_model_name_expected',
            ],
            'singleton_mapped_obsolete' => [
                'input' => 'singleton_mapped_obsolete',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => null,
                    'method' => null,
                    'class' => null,
                    'type' => MageFunctionInterface::MAGE_GET_SINGLETON,
                    'di_variable_name' => null,
                    'di_variable_class' => null,

                ],
                'm1_class_name' => 'Mage_Tax_Model_Rate',
                'mapped_singleton_class_name' => 'obsolete',
                'expected' => 'singleton_mapped_obsolete_expected',
            ],
        ];
        return $data;
    }
}
