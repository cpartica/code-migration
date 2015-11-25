<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code;

class Converter
{
    /**
     * @var ProcessorInterface[]
     */
    protected $processors;

    /**
     * @var string $filePath
     */
    protected $filePath;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @param array $processors
     * @param \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
     * @param \Magento\Migration\Logger\Logger $logger
     */
    public function __construct(
        array $processors,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Logger\Logger $logger
    ) {
        $this->processors = $processors;
        $this->tokenHelper = $tokenHelper;
        $this->logger = $logger;
    }

    /**
     * @param string $fileContent
     * @return string
     */
    public function convert($fileContent)
    {
        try {
            $tokens = $this->tokenHelper->parseContent($fileContent);

            foreach ($this->processors as $processor) {
                //initial filepath
                $processor->setFilePath($this->filePath);
                $tokens = $processor->process($tokens);
                //possibly modified file path by process
                $this->setFilePath($processor->getFilePath());
            }

            $convertedContent = $this->tokenHelper->reconstructContent($tokens);
            return $convertedContent;
        } catch (\Exception $e) {
            $this->logger->error('Caught exception: ' . $e->getMessage());
        }
        return $fileContent;
    }

    /**
     * @param string $filePath
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}
