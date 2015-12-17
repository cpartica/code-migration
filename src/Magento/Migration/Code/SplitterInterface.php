<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Migration\Code;


interface SplitterInterface
{
    /**
     * Take an array of tokens as input and, return modified array of remaining tokens and resulting files
     *
     * @param array $tokens
     * @param array $resultFiles
     * @return array
     */
    public function split(array $tokens, &$resultFiles);

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
