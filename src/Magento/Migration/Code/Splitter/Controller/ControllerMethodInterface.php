<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Splitter\Controller;

interface ControllerMethodInterface
{
    /**
     * @return array
     */
    public function getMethodTokens();

    /**
     * @param array $tokens
     * @param int $index
     * @return $this
     */
    public function setContext(array &$tokens, $index = 0);

    /**
     * @return string
     */
    public function getMethodName();

    /**
     * @return int
     */
    public function getStartIndex();

    /**
     * @return int
     */
    public function getEndIndex();

    /**
     * @return $this
     */
    public function convertToM2();
}
