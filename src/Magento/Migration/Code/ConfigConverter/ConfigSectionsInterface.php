<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter;

interface ConfigSectionsInterface
{
    /**
     * @param string $file
     * @param \Magento\Framework\Simplexml\Config $xmlConfig
     * @return ConfigTypeInterface[]||false
     */
    public function extract($file, $xmlConfig);
}
