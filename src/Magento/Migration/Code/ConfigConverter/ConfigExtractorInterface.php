<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter;

interface ConfigExtractorInterface
{
    /**
     * loops through etc files in an M1 layout xml format
     *
     * @param string $configFile
     * @return ConfigTypeInterface[]|null
     */
    public function getConfigTypes($configFile);
}
