<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class RegistryTest extends AbstractMageFunctionTestCase
{
    /**
     * @var Registry
     */
    protected $obj;

    /**
     * @return Registry
     */
    protected function getSubjectUnderTest()
    {
        return new Registry(
            $this->classMapperMock,
            $this->aliasMapperMock,
            $this->loggerMock,
            $this->tokenHelper,
            $this->argumentFactoryMock
        );
    }

    /**
     * @dataProvider registryDataProvider
     * @param $inputFile
     * @param $index
     * @param $attrs
     * @param $expectedFile
     */
    public function testRegistry(
        $inputFile,
        $index,
        $attrs,
        $expectedFile
    ) {
        $this->executeTestAgainstExpectedFile($inputFile, $index, $attrs, $expectedFile);
    }

    public function registryDataProvider()
    {
        $data = [
            'registry' => [
                'input' => 'registry',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 33,
                    'method' => 'register',
                    'class' => '\Magento\Framework\Registry',
                    'type' => MageFunctionInterface::MAGE_REGISTRY,
                    'di_variable_name' => 'registry',
                    'di_variable_class' => '\Magento\Framework\Registry',

                ],
                'expected' => 'registry_expected',
            ],
        ];
        return $data;
    }
}
