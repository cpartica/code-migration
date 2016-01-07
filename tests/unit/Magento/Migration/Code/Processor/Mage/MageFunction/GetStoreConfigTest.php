<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class GetStoreConfigTest extends AbstractMageFunctionTestCase
{
    /**
     * @var GetStoreConfig
     */
    protected $obj;

    /**
     * @return GetStoreConfig
     */
    protected function getSubjectUnderTest()
    {
        return new GetStoreConfig(
            $this->classMapperMock,
            $this->aliasMapperMock,
            $this->loggerMock,
            $this->tokenHelper,
            $this->argumentFactoryMock
        );
    }

    /**
     * @dataProvider dataProvider
     * @param $inputFile
     * @param $index
     * @param $attrs
     * @param $expectedFile
     */
    public function testGetStoreConfig(
        $inputFile,
        $index,
        $attrs,
        $expectedFile
    ) {
        $this->executeTestAgainstExpectedFile($inputFile, $index, $attrs, $expectedFile);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        $data = [
            'get_store_config_without_store' => [
                'input' => 'get_store_config_without_store',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 36,
                    'method' => 'getStoreConfig',
                    'class' => '\Magento\Framework\App\Config\ScopeConfigInterface',
                    'type' => MageFunctionInterface::MAGE_GET_STORE_CONFIG,
                    'di_variable_name' => 'scopeConfig',
                    'di_variable_class' => '\Magento\Framework\App\Config\ScopeConfigInterface',

                ],
                'expected' => 'get_store_config_without_store_expected',
            ],
            'get_store_config_with_store' => [
                'input' => 'get_store_config_with_store',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 39,
                    'method' => 'getStoreConfig',
                    'class' => '\Magento\Framework\App\Config\ScopeConfigInterface',
                    'type' => MageFunctionInterface::MAGE_GET_STORE_CONFIG,
                    'di_variable_name' => 'scopeConfig',
                    'di_variable_class' => '\Magento\Framework\App\Config\ScopeConfigInterface',

                ],
                'expected' => 'get_store_config_with_store_expected',
            ],
        ];
        return $data;
    }
}
