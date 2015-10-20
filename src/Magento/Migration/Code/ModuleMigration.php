<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code;

use Magento\Migration\Code\ModuleMigration\ModuleFileCopier;
use Magento\Migration\Code\ModuleMigration\ModuleFileExtractor;
use Magento\Framework\ObjectManagerInterface;

class ModuleMigration
{
    /**
     * @var string
     */
    protected $m2Path;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\ModuleMigration\ModuleFileExtractorFactory
     */
    protected $moduleFileExtractorFactory;

    /**
     * @var \Magento\Migration\Code\ModuleMigration\ModuleFileCopierFactory
     */
    protected $moduleFileCopierFactory;

    /**
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Code\ModuleMigration\ModuleFileExtractorFactory $moduleFileExtractorFactory
     * @param \Magento\Migration\Code\ModuleMigration\ModuleFileCopierFactory $moduleFileCopierFactory
     * @param string $m2Path
     */
    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\ModuleMigration\ModuleFileExtractorFactory $moduleFileExtractorFactory,
        \Magento\Migration\Code\ModuleMigration\ModuleFileCopierFactory $moduleFileCopierFactory,
        $m2Path
    ) {
        $this->m2Path = $m2Path;
        $this->logger = $logger;
        $this->moduleFileExtractorFactory = $moduleFileExtractorFactory;
        $this->moduleFileCopierFactory = $moduleFileCopierFactory;
    }

    /**
     * @param array $namespaces
     * @param string $codePool
     * @return array
     */
    public function moveModuleFiles($namespaces, $codePool = 'local')
    {
        $this->logger->info('Processing M1 codepool', ['stage' => 'M1', 'codePool' => $codePool]);
        if (is_array($namespaces)) {
            foreach ($namespaces as $modules) {
                if (is_array($namespaces)) {
                    foreach ($modules as $moduleConf) {
                        $extractor = $this->moduleFileExtractorFactory->create(['configFile' => $moduleConf]);
                        $moduleName = $extractor->getModuleName();
                        $this->logger->info('Processing Module', ['module' => $moduleName, 'pool' => $codePool]);

                        $moduleFiles = $this->mergeModuleFolders($extractor, $codePool);

                        $this->logger->debug(
                            'Processing Files',
                            ['module' => $moduleName, 'pool' => $codePool, 'file' => print_r($moduleFiles, true)]
                        );

                        $this->logger->info(
                            'Converting {cnt} files found in module',
                            ['stage' => 'M2', 'module' => $moduleName, 'cnt' => $this->countFilesInModule($moduleFiles)]
                        );

                        //copy all in M2 module format
                        $copier = $this->moduleFileCopierFactory->create(
                            [
                                'outputFolder' => $this->m2Path,
                                'module' => $moduleName
                            ]
                        );
                        if ($copier->createM2ModuleFolder() !== null) {
                            foreach ($moduleFiles as $fileContentFolder => $files) {
                                $copier->copyM2Files($files, $fileContentFolder);
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param ModuleFileExtractor $extractor
     * @param string $codePool
     * @return array
     */
    protected function mergeModuleFolders($extractor, $codePool)
    {
        return array_merge(
            $extractor->getTranslationsFromFiles($extractor->getTranslationsFromConfig()),
            $extractor->getModelsFromFiles($codePool),
            $extractor->getHelpersFromFiles($codePool),
            $extractor->getBlockFromFiles($codePool),
            $extractor->getControllersFromFiles($codePool),
            $extractor->getEtcFromFiles($codePool),
            $extractor->getViewLayoutXmlFromFiles(
                $extractor->getViewFromConfig('frontend'),
                'frontend'
            ),
            $extractor->getViewTemplatesFromFiles(
                array_values($extractor->getViewLayoutXmlFromFiles(
                    $extractor->getViewFromConfig('frontend'),
                    'frontend'
                ))[0],
                'frontend'
            ),
            $extractor->getSkinJsFromFiles(
                array_values($extractor->getViewLayoutXmlFromFiles(
                    $extractor->getViewFromConfig('frontend'),
                    'frontend'
                ))[0],
                'frontend'
            ),
            $extractor->getViewLayoutXmlFromFiles(
                $extractor->getViewFromConfig('adminhtml'),
                'adminhtml'
            ),
            $extractor->getViewTemplatesFromFiles(
                array_values($extractor->getViewLayoutXmlFromFiles(
                    $extractor->getViewFromConfig('adminhtml'),
                    'adminhtml'
                ))[0],
                'adminhtml'
            ),
            $extractor->getSkinJsFromFiles(
                array_values($extractor->getViewLayoutXmlFromFiles(
                    $extractor->getViewFromConfig('adminhtml'),
                    'adminhtml'
                ))[0],
                'adminhtml'
            )
        );
    }

    /**
     * @param string $filesArray ;
     * @return int
     */
    protected function countFilesInModule($filesArray)
    {
        $cnt = 0;
        if (is_array($filesArray)) {
            foreach ($filesArray as $files) {
                $cnt += count($files);
            }
        }
        return $cnt;
    }
}
