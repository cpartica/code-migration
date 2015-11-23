<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\ConfigExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class ConfigSectionsEav extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /**
     * @var string
     */
    protected $fileName = 'eav_attributes';

    /**
     * @var array
     */
    protected $locations = [
        'global/eav_attributes' => '.'
    ];

    /**
     * @var string[]
     */
    protected $xsls = [
        'config.xsl',
        'removeFirstTag.xsl',
        'transTagToAttr.xsl'
    ];

    /**
     * @var string
     */
    protected $tagName = 'entity';

    /**
     * @var string
     */
    protected $xmlSchema = 'urn:magento:module:Magento_Eav:etc/eav_attributes.xsd';
}
