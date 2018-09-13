<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code;

use Magento\Migration\Code\ModuleMigration;

/**
 * Class ModuleMigrationTest
 */
class ModuleMigrationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Migration\Code\ModuleMigration
     */
    protected $model;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleFileExtractorFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleFileCopierFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleEnablerConfigFactory;

    /**
     * @var string
     */
    protected $m2Path;

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

        $className = '\Magento\Migration\Logger\Logger';
        $this->logger = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $className = '\Magento\Migration\Code\ModuleMigration\ModuleFileExtractorFactory';
        $this->moduleFileExtractorFactory = $this->getMockBuilder($className)->setMethods(['create'])->getMock();

        $className = '\Magento\Migration\Code\ModuleMigration\ModuleFileCopierFactory';
        $this->moduleFileCopierFactory = $this->getMockBuilder($className)->setMethods(['create'])->getMock();

        $className = '\Magento\Migration\Utility\M1\ModuleEnablerConfigFactory';
        $this->moduleEnablerConfigFactory = $this->getMockBuilder($className)->setMethods(['create'])->getMock();

        $this->m2Path = '/path/to/m2';

        $this->model = $this->objectManager->getObject(
            '\Magento\Migration\Code\ModuleMigration',
            [
                'logger' => $this->logger,
                'moduleFileExtractorFactory' => $this->moduleFileExtractorFactory,
                'moduleFileCopierFactory' => $this->moduleFileCopierFactory,
                'moduleEnablerConfigFactory' => $this->moduleEnablerConfigFactory,
                'm2Path' => $this->m2Path,
            ]
        );
    }

    /**
     * @dataProvider moveModuleFilesProvider
     * @param array $namespaces
     * @param string $codePool
     * test MoveModuleFiles
     */
    public function testMoveModuleFiles($namespaces, $codePool)
    {
        $arrayBlockFiles = ['Block' => [$this->m2Path . '/app/code/Vendor1/Module1/Block/file1']];
        $arrayFrontendXMLFiles = ['frontendXml' => [$this->m2Path . '/app/code/Vendor1/Module1/Block/file2']];

        $className = '\Magento\Migration\Code\ModuleMigration\ModuleFileCopier';
        $moduleFileCopier = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $className = '\Magento\Migration\Code\ModuleMigration\ModuleFileExtractor';
        $moduleFileExtractor = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $className = '\Magento\Migration\Utility\M1\ModuleEnablerConfig';
        $moduleEnablerConfigFactory = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $this->moduleFileExtractorFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($moduleFileExtractor);

        $this->moduleFileCopierFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($moduleFileCopier);

        $this->moduleEnablerConfigFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($moduleEnablerConfigFactory);

        $moduleEnablerConfigFactory->expects($this->atLeastOnce())
                    ->method('isModuleEnabled')
                    ->willReturn(true);

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getModuleName')
            ->willReturn('Vendor1_Module1');

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getViewLayoutXmlFromFiles')
            ->willReturn($arrayFrontendXMLFiles);

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getTranslationsFromConfig')
            ->willReturn([]);

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getTranslationsFromFiles')
            ->willReturn([]);

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getModelsFromFiles')
            ->willReturn([]);

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getHelpersFromFiles')
            ->willReturn([]);

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getBlockFromFiles')
            ->willReturn($arrayBlockFiles);

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getControllersFromFiles')
            ->willReturn([]);

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getEtcFromFiles')
            ->willReturn([]);

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getViewLayoutXmlFromFiles')
            ->willReturn([]);

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getViewTemplatesFromFiles')
            ->willReturn([]);

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getSkinJsFromFiles')
            ->willReturn([]);

        $moduleFileExtractor->expects($this->atLeastOnce())
            ->method('getViewFromConfig')
            ->willReturn([]);


        $moduleFileCopier->expects($this->atLeastOnce())
            ->method('createM2ModuleFolder')
            ->willReturn(true);

        $files = current($arrayBlockFiles);
        $key = key($arrayBlockFiles);
        $moduleFileCopier->expects($this->at(1))
            ->method('copyM2Files')
            ->with($files, $key);


        $this->model->moveModuleFiles($namespaces, $codePool);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function moveModuleFilesProvider()
    {
        return [
            [
                [
                    'Vendor1' => [
                        'Module1' => $this->m2Path . '/Vendor1/Module1/etc/config.xml',
                        'Module2' => $this->m2Path . '/Vendor1/Module2/etc/config.xml',
                        'Module3' => $this->m2Path . '/Vendor1/Module3/etc/config.xml',
                    ],

                    'Vendor2' => [
                        'Module1' => $this->m2Path . '/Vendor2/Module1/etc/config.xml',
                        'Module2' => $this->m2Path . '/Vendor2/Module2/etc/config.xml',
                        'Module3' => $this->m2Path . '/Vendor2/Module3/etc/config.xml',
                    ],
                ],
                'local',
            ],
        ];
    }
}
