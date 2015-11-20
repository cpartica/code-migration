<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\SystemExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class SystemSections extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /** @var string */
    protected $fileName = 'system';
    /**
     * @var array
     */
    protected $locations = ['adminhtml'];
}
