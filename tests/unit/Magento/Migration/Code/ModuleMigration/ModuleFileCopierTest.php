<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ModuleMigration;

use Magento\Migration\Code\ModuleMigration\ModuleFileCopier;

/**
 * Class ModuleFileCopierTest
 */
class ModuleFileCopierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ModuleFileCopier
     */
    protected $model;

    /**
     * @var string
     */
    protected $outputFolder;

    /**
     * @var string
     */
    protected $module;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $file;

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

        $this->outputFolder = __DIR__ . '/../_files/m2Out';

        $this->module = 'Mage_Catalog';

        $className = '\Magento\Framework\Filesystem\Driver\File';
        $this->file = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $this->file->expects($this->any())
            ->method('isExists')
            ->willReturn(true);

        $this->model = $this->objectManager->getObject(
            'Magento\Migration\Code\ModuleMigration\ModuleFileCopier',
            [
                'outputFolder' => $this->outputFolder,
                'module' => $this->module,
                'file' => $this->file,
            ]
        );
    }

    /**
     * test CreateM2ModuleFolder
     */
    public function testCreateM2ModuleFolder()
    {
        $result = $this->model->createM2ModuleFolder();
        $this->assertEquals($this->outputFolder . '/app/code/Mage/Catalog', $result);
    }

    /**
     * test CopyM2Files
     * @param string $contentFolder
     * @param string[] $files
     * @dataProvider copyM2FilesProvider
     */
    public function testCopyM2Files($contentFolder, $files)
    {

        $this->file->expects($this->once())
            ->method('copy');

        $this->model->copyM2Files($files, $contentFolder);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function copyM2FilesProvider()
    {
        return [
            [
                'type' => 'i18n',
                'files' => [
                    'path/app/locale/en_US/file.csv',
                    ],
            ],
            [
                'type' => 'frontendWeb',
                'files' => [
                    'path/app/skin/frontend/base/default/package/css/file.css',
                ],
            ],
            [
                'type' => 'frontendWeb',
                'files' => [
                    'path/app/js/package/css/file.js',
                ],
            ],
            [
                'type' => 'frontendXml',
                'files' => [
                    'path/app/design/frontend/base/default/layout/file.xml',
                ],
            ],
            [
                'type' => 'frontendPhtml',
                'files' => [
                    'path/app/design/frontend/base/default/template/package/file.phtml',
                ],
            ],
            [
                'type' => 'adminhtmlWeb',
                'files' => [
                    'path/app/skin/adminhtml/default/default/package/css/file.css',
                ],
            ],
            [
                'type' => 'adminhtmlWeb',
                'files' => [
                    'path/app/js/package/css/file.js',
                ],
            ],
            [
                'type' => 'adminhtmlXml',
                'files' => [
                    'path/app/design/adminhtml/default/default/layout/file.xml',
                ],
            ],
            [
                'type' => 'adminhtmlPhtml',
                'files' => [
                    'path/app/design/adminhtml/default/default/template/package/file.phtml',
                ],
            ],
            [
                'type' => 'Models',
                'files' => [
                    'path/app/code/Mage/Catalog/Models/model.php',
                ],
            ],
        ];
    }
}
