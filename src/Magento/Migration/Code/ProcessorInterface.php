<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code;

interface ProcessorInterface
{
    /**
     * Take an array of tokens as input, return modified array of tokens
     *
     * @param array $tokens
     * @return array
     */
    public function process(array $tokens);

    /**
     * @param string $filePath
     * @return $this
     */
    public function setFilePath($filePath);

    /**
     * @return string
     */
    public function getFilePath();
}
