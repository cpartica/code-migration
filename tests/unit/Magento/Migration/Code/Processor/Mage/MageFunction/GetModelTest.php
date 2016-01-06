<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;
use Magento\Migration\Code\TestCase;
use Magento\Migration\Mapping\Alias;

class GetModelTest extends TestCase
{
    /**
     * @var GetModel
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

    /**
     * @var \Magento\Migration\Code\Processor\NamingHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $namingHelperMock;

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

        $this->obj = new GetModel(
            $this->classMapperMock,
            $this->aliasMapperMock,
            $this->loggerMock,
            $this->tokenHelper,
            $this->argumentFactoryMock,
            $this->namingHelperMock
        );
    }

    /**
     * @dataProvider getModelDataProvider
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
    ) {
        $file = __DIR__ . '/_files/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);

        $this->obj->setContext($tokens, $index);

        $this->aliasMapperMock->expects($this->any())
            ->method('mapAlias')
            ->with('tax', 'model')
            ->willReturn('Mage_Tax_Model');

        $this->classMapperMock->expects($this->any())
            ->method('mapM1Class')
            ->with($m1ClassName)
            ->willReturn($mappedModelClass);

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

    public function getModelDataProvider()
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
