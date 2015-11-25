<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\ConfigExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class ConfigSectionsEvents extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /**
     * @var string
     */
    protected $fileName = 'events';

    /**
     * @var array
     */
    protected $locations = [
        'global/events' => '.',
        'crontab/events' => '.',
        'frontend/events' => 'frontend',
        'adminhtml/events' => 'adminhtml',
        'admin/events' => 'adminhtml',
    ];

    /**
     * @var string[]
     */
    protected $xsls = [
        'config.xsl',
        'removeFirstTag.xsl',
        'transTagToAttr.xsl',
        'eventsObservers.xsl'
    ];

    /**
     * @var string
     */
    protected $tagName = 'event';

    /**
     * @var string
     */
    protected $xmlSchema = 'urn:magento:framework:Event/etc/events.xsd';
}
