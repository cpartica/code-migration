<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code;

use Magento\Framework\ObjectManagerInterface;

class EtcConverter
{
    /**
     * @var \Magento\Migration\Code\EtcConverter\EtcExtractorInterface[]
     */
    protected $extractors;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\EtcConverter\EtcFileFactory
     */
    protected $etcFileFactory;

    /**
     * @param array $extractors
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Migration\Code\EtcConverter\EtcFileFactory $etcFileFactory
     */
    public function __construct(
        array $extractors,
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Migration\Code\EtcConverter\EtcFileFactory $etcFileFactory
    ) {
        $this->file = $file;
        $this->logger = $logger;
        $this->extractors = $extractors;
        $this->etcFileFactory = $etcFileFactory;
    }

    /**
     * @param string $file
     * @return false|int
     */
    public function processConfig($file)
    {
        foreach ($this->extractors as $extractor) {
            $extractor->setFile($file);
            $etcTypes = $extractor->getEtcTypes();
            if (is_array($etcTypes)) {
                array_walk_recursive($etcTypes, function ($etcType) {
                    if ($etcType) {
                        /** @var \Magento\Migration\Code\EtcConverter\EtcTypeInterface $etcType */
                        $fileHandler = $this->etcFileFactory->create(['etcType' => $etcType]);
                        if ($fileHandler->createFileHandler()) {
                            $this->logger->info('Created M2 config file ' . $etcType->getFileName());
                        } else {
                            $this->logger->warn('Error creating M2 config file ' . $etcType->getFileName());
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
    protected function deleteM1EtcFile($file)
    {
        if ($this->file->deleteFile($file)) {
            $this->logger->info('Deleted M1 config file' . $file);
        } else {
            $this->logger->warn('Error deleting M1 config file ' . $file);
        }
    }
}
