<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Block;

interface BlockFunctionInterface
{
    /**
     * @param array $tokens
     * @param int $index
     * @return $this
     */
    public function setContext(array &$tokens, $index = 0);

    /**
     * @return $this
     */
    public function convertToM2();
}
