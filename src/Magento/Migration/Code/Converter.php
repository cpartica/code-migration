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
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @param array $processors
     * @param \Magento\Migration\Logger\Logger $logger
     */
    public function __construct(
        array $processors,
        \Magento\Migration\Logger\Logger $logger
    ) {
        $this->processors = $processors;
        $this->logger = $logger;
    }

    /**
     * @param string $fileContent
     * @return string
     */
    public function convert($fileContent)
    {
        try {
            $tokens = token_get_all($fileContent);

            for ($i = 0; $i < count($tokens); $i++) {
                if (is_array($tokens[$i])) {
                    $tokens[$i][] = token_name($tokens[$i][0]);
                }
            }
            foreach ($this->processors as $processor) {
                $tokens = $processor->process($tokens);
            }

            $convertedContent = '';
            foreach ($tokens as $token) {
                if (is_array($token)) {
                    $convertedContent .= $token[1];
                } else {
                    $convertedContent .= $token;
                }
            }
            return $convertedContent;
        } catch (\Exception $e) {
            $this->logger->error('Caught exception: ' . $e->getMessage());
        }
        return $fileContent;
    }
}
