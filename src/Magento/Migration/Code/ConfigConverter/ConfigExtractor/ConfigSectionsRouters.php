<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\ConfigExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class ConfigSectionsRouters extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /**
     * @var string
     */
    protected $fileName = 'routes';
    /**
     * @var array
     */
    protected $locations = [
        'global/routers' => '.',
        'frontend/routers' => 'frontend',
        'adminhtml/routers' => 'adminhtml',
        'admin/routers' => 'adminhtml',
    ];
}
