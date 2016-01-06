<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;
use Magento\Migration\Code\TestCase;

class AppTest extends TestCase
{
    /**
     * @var App
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

        $this->obj = new App(
            $this->classMapperMock,
            $this->aliasMapperMock,
            $this->loggerMock,
            $this->tokenHelper,
            $this->argumentFactoryMock
        );
    }

    /**
     * @dataProvider appDataProvider
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

    public function appDataProvider()
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
