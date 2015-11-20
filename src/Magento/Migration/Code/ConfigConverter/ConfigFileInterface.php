<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter;

interface ConfigFileInterface
{
    /**
     * @return int|void
     */
    public function createFile();
}
