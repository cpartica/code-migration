<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ModuleMigration;

use Magento\Migration\Code\ModuleMigration\ModuleFileExtractor;

/**
 * Class ModuleFileExtractorTest
 */
class ModuleFileExtractorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ModuleFileExtractor
     */
    protected $model;
    /**
     * @var string
     */
    protected $configFile;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileUtilFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileUtil;

    /**
     * @var \Magento\Framework\Simplexml\Config
     */
    protected $configXML;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->configFile = __DIR__ . '/../_files/app/code/core/Mage/Catalog/etc/config.xml';

        $className = '\Magento\Framework\Simplexml\ConfigFactory';
        $this->configFactory = $this->getMockBuilder($className)
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $className = '\Magento\Migration\Utility\M1\FileFactory';
        $this->fileUtilFactory = $this->getMockBuilder($className)
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $className = '\Magento\Migration\Utility\M1\File';
        $this->fileUtil = $this->objectManager->getObject($className, ['basePath' => __DIR__ . '/../_files']);
        $this->configXML = $this->fileUtil;

        $this->fileUtilFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->fileUtil);

        $className = '\Magento\Framework\Simplexml\Config';
        $this->configXML =$this->objectManager->getObject($className);

        $this->configFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->configXML);


        $this->model = $this->objectManager->getObject(
            'Magento\Migration\Code\ModuleMigration\ModuleFileExtractor',
            [
                'configFile' => $this->configFile,
                'configFactory' => $this->configFactory,
                'fileUtilFactory' => $this->fileUtilFactory,
            ]
        );
    }

    /**
     * test GetModuleName
     */
    public function testGetModuleName()
    {
        $result = $this->model->getModuleName();
        $this->assertEquals('Mage_Catalog', $result);
    }

    /**
     * test GetTranslationsFromConfig
     */
    public function testGetTranslationsFromConfig()
    {
        $result =  $this->model->getTranslationsFromConfig();
        $this->assertEquals(["Mage_Catalog.csv" => "Mage_Catalog.csv"], $result);
    }

    /**
     * test GetViewFromConfig
     */
    public function testGetViewFromConfig()
    {
        $result = $this->model->getViewFromConfig('frontend');
        $this->assertEquals(["catalog.xml" => "catalog.xml", "catalog_msrp.xml" => "catalog_msrp.xml"], $result);
    }

    /**
     * test GetViewLayoutXmlFromFiles
     */
    public function testGetViewLayoutXmlFromFiles()
    {
        $files = ["catalog.xml" => "catalog.xml"];
        $result = $this->model->getViewLayoutXmlFromFiles($files, 'frontend');
        $this->assertArrayHasKey('frontendXml', $result);
        $this->assertEquals(key($files), basename(current($result['frontendXml'])));
        $result = $this->model->getViewLayoutXmlFromFiles($files, 'adminhtml');
        $this->assertArrayHasKey('adminhtmlXml', $result);
        $this->assertEquals(key($files), basename(current($result['adminhtmlXml'])));
    }

    /**
     * test GetViewTemplatesFromFiles
     */
    public function testGetViewTemplatesFromFiles()
    {
        $files = [__DIR__ . '/../_files/app/design/frontend/base/default/layout/catalog.xml'];
        $result = $this->model->getViewTemplatesFromFiles($files, 'frontend');
        $this->assertEquals('frontendPhtml', key($result));
        $this->assertEquals(59, count(current($result)));

        $files = [__DIR__ . '/../_files/app/design/adminhtml/default/default/layout/catalog.xml'];
        $result = $this->model->getViewTemplatesFromFiles($files, 'adminhtml');
        $this->assertEquals('adminhtmlPhtml', key($result));
        $this->assertEquals(50, count(current($result)));
    }

    /**
     * test GetSkinJsFromFiles
     */
    public function testGetSkinJsFromFiles()
    {
        $files = [__DIR__ . '/../_files/app/design/adminhtml/default/default/layout/catalog.xml'];
        $result = $this->model->getSkinJsFromFiles($files, 'adminhtml');
        $this->assertEquals('adminhtmlWeb', key($result));
        $this->assertEquals(0, count(current($result)));

        $files = [__DIR__ . '/../_files/app/design/frontend/base/default/layout/catalog.xml'];
        $result = $this->model->getSkinJsFromFiles($files, 'frontend');
        $this->assertEquals('frontendWeb', key($result));
        $this->assertEquals(6, count(current($result)));
    }

    /**
     * test GetTranslationsFromFiles
     */
    public function testGetTranslationsFromFiles()
    {
        $files = ["Mage_Catalog.csv" => "Mage_Catalog.csv"];
        $result = $this->model->getTranslationsFromFiles($files);
        $this->assertArrayHasKey('i18n', $result);
        $this->assertEquals(key($files), basename(current($result['i18n'])));
    }

    /**
     * test GetFromFiles
     */
    public function testGetFromFiles()
    {
        $result = $this->model->getFromFiles('Model', 'core', "inc");
        $this->assertEquals(0, count($result));

        $result = $this->model->getFromFiles('Folder', 'core', "inc");
        $this->assertEquals(0, count($result));

        $result = $this->model->getFromFiles('Model', 'local', "php");
        $this->assertEquals(0, count($result));

        $result = $this->model->getFromFiles('Model', 'core', "php");
        $this->assertEquals(269, count($result));
    }

    /**
     * test GetModelsFromFiles
     */
    public function testGetModelsFromFiles()
    {
        $result = $this->model->getModelsFromFiles('core');
        $this->assertEquals('Model', key($result));
        $this->assertEquals(269, count(current($result)));
    }

    /**
     * test GetHelpersFromFiles
     */
    public function testGetHelpersFromFiles()
    {
        $result = $this->model->getHelpersFromFiles('core');
        $this->assertEquals('Helper', key($result));
        $this->assertEquals(19, count(current($result)));
    }

    /**
     * test GetBlockFromFiles
     */
    public function testGetBlockFromFiles()
    {
        $result = $this->model->getBlockFromFiles('core');
        $this->assertEquals('Block', key($result));
        $this->assertEquals(56, count(current($result)));
    }

    /**
     * test GetControllersFromFiles
     */
    public function testGetControllersFromFiles()
    {
        $result = $this->model->getControllersFromFiles('core');
        $this->assertEquals('Controller', key($result));
        $this->assertEquals(5, count(current($result)));
    }

    /**
     * test GetEtcFromFiles
     */
    public function testGetEtcFromFiles()
    {
        $result = $this->model->getEtcFromFiles('core');
        $this->assertEquals('etc', key($result));
        $this->assertEquals(9, count(current($result)));
    }
}
