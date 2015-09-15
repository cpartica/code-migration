<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage;

interface MatcherInterface
{
    /**
     * @param array $tokens
     * @param int $index
     * @return MageFunctionInterface|null
     */
    public function match(&$tokens, $index);
}
