<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\ConfigExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class ConfigSectionsCrontab extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /**
     * @var string
     */
    protected $fileName = 'crontab';

    /**
     * @var array
     */
    protected $locations = [
        'crontab/jobs' => '.',
        '*/crontab/jobs' => '.'
    ];

    /**
     * @var string[]
     */
    protected $xsls = [
        'config.xsl',
        'removeFirstTag.xsl',
        'transTagToAttr.xsl',
        'cronAddDefault.xsl'
    ];

    /**
     * @var string
     */
    protected $tagName = 'job';

    /**
     * @var string
     */
    protected $xmlSchema = 'urn:magento:module:Magento_Cron:etc/crontab.xsd';
}
