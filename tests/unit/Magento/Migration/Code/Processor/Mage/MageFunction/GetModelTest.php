<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;
use Magento\Migration\Code\Processor\NamingHelper;
use Magento\Migration\Mapping\Alias;

class GetModelTest extends AbstractMageFunctionTestCase
{
    /**
     * @var GetModel
     */
    protected $obj;

    /**
     * @var NamingHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $namingHelperMock;

    /**
     * @return GetModel
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
            ->method('getM2FactoryClassName')
            ->willReturnMap([
                ['Mage_Tax_Model_Config_Method', '\\Magento\\Tax\\Model\\Config\\MethodFactory'],
                ['Mage_Tax_Model_Rule', '\\Magento\\Tax\\Model\\RuleFactory'],
            ]);
        $this->namingHelperMock
            ->expects($this->any())
            ->method('generateVariableName')
            ->willReturnMap([
                ['\\Magento\\Tax\\Model\\Config\\MethodFactory', 'taxConfigMethodFactory'],
                ['\\Magento\\Tax\\Model\\RuleFactory', 'taxRuleFactory'],
            ]);

        return new GetModel(
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
    public function testGetModel(
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

    /**
     * @return array
     */
    public function dataProvider()
    {
        $data = [
            'model_mapped' => [
                'input' => 'model_mapped',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 36,
                    'method' => 'doSomething',
                    'class' => '\Magento\Tax\Model\Config\MethodFactory',
                    'type' => MageFunctionInterface::MAGE_GET_MODEL,
                    'di_variable_name' => 'taxConfigMethodFactory',
                    'di_variable_class' => '\Magento\Tax\Model\Config\MethodFactory',

                ],
                'm1_class_name' => 'Mage_Tax_Model_Config_Method',
                'mapped_model_class_name' => '\Magento\Tax\Model\Config\Method',
                'expected' => 'model_mapped_expected',
            ],
            'model_not_mapped' => [
                'input' => 'model_not_mapped',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 36,
                    'method' => 'doSomething',
                    'class' => '\Magento\Tax\Model\Config\MethodFactory',
                    'type' => MageFunctionInterface::MAGE_GET_MODEL,
                    'di_variable_name' => 'taxConfigMethodFactory',
                    'di_variable_class' => '\Magento\Tax\Model\Config\MethodFactory',

                ],
                'm1_class_name' => 'Mage_Tax_Model_Config_Method',
                'mapped_model_class_name' => null,
                'expected' => 'model_not_mapped_expected',
            ],
            'model_direct_model_name' => [
                'input' => 'model_direct_model_name',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 36,
                    'method' => 'doSomething',
                    'class' => '\Magento\Tax\Model\RuleFactory',
                    'type' => MageFunctionInterface::MAGE_GET_MODEL,
                    'di_variable_name' => 'taxRuleFactory',
                    'di_variable_class' => '\Magento\Tax\Model\RuleFactory',

                ],
                'm1_class_name' => 'Mage_Tax_Model_Rule',
                'mapped_model_class_name' => '\Magento\Tax\Model\Rule',
                'expected' => 'model_direct_model_name_expected',
            ],
            'model_mapped_obsolete' => [
                'input' => 'model_mapped_obsolete',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => null,
                    'method' => null,
                    'class' => null,
                    'type' => MageFunctionInterface::MAGE_GET_MODEL,
                    'di_variable_name' => null,
                    'di_variable_class' => null,

                ],
                'm1_class_name' => 'Mage_Tax_Model_Rate',
                'mapped_model_class_name' => 'obsolete',
                'expected' => 'model_mapped_obsolete_expected',
            ],
        ];
        return $data;
    }
}
