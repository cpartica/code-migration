<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\EtcConverter\ConfigExtractor;

use \Magento\Migration\Code\EtcConverter\EtcTypeInterface;

interface ConfigSectionsInterface
{
    /**
     * @param string $file
     * @param \Magento\Framework\Simplexml\Config $xmlConfig
     * @return EtcTypeInterface[]||false
     */
    public function extract($file, $xmlConfig);
}
