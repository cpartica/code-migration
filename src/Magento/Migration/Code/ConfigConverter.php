<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code;

use Magento\Framework\ObjectManagerInterface;

class ConfigConverter
{
    /**
     * @var \Magento\Migration\Code\ConfigConverter\ConfigExtractorInterface[]
     */
    protected $extractors;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\ConfigConverter\ConfigFileFactory
     */
    protected $configFileFactory;

    /**
     * @param array $extractors
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Migration\Code\ConfigConverter\ConfigFileFactory $configFileFactory
     */
    public function __construct(
        array $extractors,
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Migration\Code\ConfigConverter\ConfigFileFactory $configFileFactory
    ) {
        $this->file = $file;
        $this->logger = $logger;
        $this->extractors = $extractors;
        $this->configFileFactory = $configFileFactory;
    }

    /**
     * @param string $file
     * @return false|int
     */
    public function processConfig($file)
    {
        foreach ($this->extractors as $extractor) {
            $configTypes = $extractor->getConfigTypes($file);
            if (is_array($configTypes)) {
                array_walk_recursive($configTypes, function ($configType) {
                    if ($configType) {
                        /** @var \Magento\Migration\Code\ConfigConverter\ConfigTypeInterface $configType */
                        $fileHandler = $this->configFileFactory->create(['configType' => $configType]);
                        if ($fileHandler->createFile()) {
                            $this->logger->info('Created M2 config file ' . $configType->getFileName());
                        } else {
                            $this->logger->warn('Error creating M2 config file ' . $configType->getFileName());
                        }
                    }
                });
            }
        }
    }

    /**
     * @param string $file
     * @return void
     */
    protected function deleteM1ConfigFile($file)
    {
        if ($this->file->deleteFile($file)) {
            $this->logger->info('Deleted M1 config file' . $file);
        } else {
            $this->logger->warn('Error deleting M1 config file ' . $file);
        }
    }
}
