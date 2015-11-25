<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\ConfigExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class ConfigSectionsModule extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /**
     * @var string
     */
    protected $fileName = 'module';

    /**
     * @var array
     */
    protected $locations = [
        'modules' => '.'
    ];

    /**
     * @var string[]
     */
    protected $xsls = [
        'config.xsl',
        'removeFirstTag.xsl',
        'moduleMod.xsl'
    ];

    /**
     * @var string
     */
    protected $xmlSchema = 'urn:magento:framework:Module/etc/module.xsd';
}
