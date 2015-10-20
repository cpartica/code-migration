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
                $tokens = $processor->process($tokens);
            }

            $convertedContent = $this->tokenHelper->reconstructContent($tokens);
            return $convertedContent;
        } catch (\Exception $e) {
            $this->logger->error('Caught exception: ' . $e->getMessage());
        }
        return $fileContent;
    }
}
