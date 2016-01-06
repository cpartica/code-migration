<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class AppTest extends AbstractMageFunctionTestCase
{
    /**
     * @var App
     */
    protected $obj;

    /**
     * @return App
     */
    protected function getSubjectUnderTest()
    {
        return new App(
            $this->classMapperMock,
            $this->aliasMapperMock,
            $this->loggerMock,
            $this->tokenHelper,
            $this->argumentFactoryMock
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
    public function testApp(
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
            'app_get_locale' => [
                'input' => 'app_get_locale',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => null,
                    'method' => 'getLocale',
                    'class' => null,
                    'type' => MageFunctionInterface::MAGE_APP,
                    'di_variable_name' => null,
                    'di_variable_class' => null,

                ],
                'm1_class_name' => 'Mage_Core_Model_Locale',
                'mapped_class_name' => 'obsolete',
                'expected' => 'app_get_locale_expected',
            ],
            'app_get_layout' => [
                'input' => 'app_get_layout',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 39,
                    'method' => 'getLayout',
                    'class' => '\Magento\Framework\View\LayoutInterface',
                    'type' => MageFunctionInterface::MAGE_APP,
                    'di_variable_name' => 'layout',
                    'di_variable_class' => '\Magento\Framework\View\LayoutInterface',

                ],
                'm1_class_name' => 'Mage_Core_Model_Layout',
                'mapped_class_name' => '\Magento\Framework\View\LayoutInterface',
                'expected' => 'app_get_layout_expected',
            ],
            'app_get_translator' => [
                'input' => 'app_get_translator',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 39,
                    'method' => 'getTranslator',
                    'class' => '\Magento\Framework\TranslateInterface',
                    'type' => MageFunctionInterface::MAGE_APP,
                    'di_variable_name' => 'translator',
                    'di_variable_class' => '\Magento\Framework\TranslateInterface',

                ],
                'm1_class_name' => 'Mage_Core_Model_Translate',
                'mapped_class_name' => '\Magento\Framework\TranslateInterface',
                'expected' => 'app_get_translator_expected',
            ],
            'app_get_config' => [
                'input' => 'app_get_config',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => null,
                    'method' => 'getConfig',
                    'class' => null,
                    'type' => MageFunctionInterface::MAGE_APP,
                    'di_variable_name' => null,
                    'di_variable_class' => null,

                ],
                'm1_class_name' => 'Mage_Core_Model_Config',
                'mapped_class_name' => 'obsolete',
                'expected' => 'app_get_config_expected',
            ],
            'app_get_request' => [
                'input' => 'app_get_request',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 39,
                    'method' => 'getRequest',
                    'class' => '\Magento\Framework\App\Request\Http',
                    'type' => MageFunctionInterface::MAGE_APP,
                    'di_variable_name' => 'request',
                    'di_variable_class' => '\Magento\Framework\App\Request\Http',

                ],
                'm1_class_name' => 'Mage_Core_Controller_Request_Http',
                'mapped_class_name' => '\Magento\Framework\App\Request\Http',
                'expected' => 'app_get_request_expected',
            ],
            'app_get_store' => [
                'input' => 'app_get_store',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 35,
                    'method' => 'getStore',
                    'class' => '\Magento\Store\Model\StoreManagerInterface',
                    'type' => MageFunctionInterface::MAGE_APP,
                    'di_variable_name' => 'storeManager',
                    'di_variable_class' => '\Magento\Store\Model\StoreManagerInterface',

                ],
                'm1_class_name' => 'Mage_Core_Controller_Request_Http',
                'mapped_class_name' => null,
                'expected' => 'app_get_store_expected',
            ],
            'app_get_stores' => [
                'input' => 'app_get_stores',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 35,
                    'method' => 'getStores',
                    'class' => '\Magento\Store\Model\StoreManagerInterface',
                    'type' => MageFunctionInterface::MAGE_APP,
                    'di_variable_name' => 'storeManager',
                    'di_variable_class' => '\Magento\Store\Model\StoreManagerInterface',

                ],
                'm1_class_name' => 'Mage_Core_Controller_Request_Http',
                'mapped_class_name' => null,
                'expected' => 'app_get_stores_expected',
            ],
            'app_get_cookie' => [
                'input' => 'app_get_cookie',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 35,
                    'method' => 'getCookie',
                    'class' => '\Magento\Framework\Stdlib\CookieManagerInterface',
                    'type' => MageFunctionInterface::MAGE_APP,
                    'di_variable_name' => 'cookieManager',
                    'di_variable_class' => '\Magento\Framework\Stdlib\CookieManagerInterface',

                ],
                'm1_class_name' => 'Mage_Core_Controller_Request_Http',
                'mapped_class_name' => null,
                'expected' => 'app_get_cookie_expected',
            ],
            'app_load_cache' => [
                'input' => 'app_load_cache',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 37,
                    'method' => 'loadCache',
                    'class' => '\Magento\Framework\Cache\FrontendInterface',
                    'type' => MageFunctionInterface::MAGE_APP,
                    'di_variable_name' => 'cache',
                    'di_variable_class' => '\Magento\Framework\Cache\FrontendInterface',

                ],
                'm1_class_name' => 'Mage_Core_Controller_Request_Http',
                'mapped_class_name' => null,
                'expected' => 'app_load_cache_expected',
            ],
            'app_save_cache' => [
                'input' => 'app_save_cache',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 37,
                    'method' => 'saveCache',
                    'class' => '\Magento\Framework\Cache\FrontendInterface',
                    'type' => MageFunctionInterface::MAGE_APP,
                    'di_variable_name' => 'cache',
                    'di_variable_class' => '\Magento\Framework\Cache\FrontendInterface',

                ],
                'm1_class_name' => 'Mage_Core_Controller_Request_Http',
                'mapped_class_name' => null,
                'expected' => 'app_save_cache_expected',
            ],
            'app_remove_cache' => [
                'input' => 'app_remove_cache',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 37,
                    'method' => 'removeCache',
                    'class' => '\Magento\Framework\Cache\FrontendInterface',
                    'type' => MageFunctionInterface::MAGE_APP,
                    'di_variable_name' => 'cache',
                    'di_variable_class' => '\Magento\Framework\Cache\FrontendInterface',

                ],
                'm1_class_name' => 'Mage_Core_Controller_Request_Http',
                'mapped_class_name' => null,
                'expected' => 'app_remove_cache_expected',
            ],
            'app_clean_cache' => [
                'input' => 'app_clean_cache',
                'index' => 31,
                'attr' => [
                    'start_index' => 31,
                    'end_index' => 37,
                    'method' => 'cleanCache',
                    'class' => '\Magento\Framework\Cache\FrontendInterface',
                    'type' => MageFunctionInterface::MAGE_APP,
                    'di_variable_name' => 'cache',
                    'di_variable_class' => '\Magento\Framework\Cache\FrontendInterface',

                ],
                'm1_class_name' => 'Mage_Core_Controller_Request_Http',
                'mapped_class_name' => null,
                'expected' => 'app_clean_cache_expected',
            ],
        ];
        return $data;
    }
}
