<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\ConfigExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class ConfigSectionsSales extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /**
     * @var string
     */
    protected $fileName = 'sales';

    /**
     * @var array
     */
    protected $locations = [
        'global/sales' => '.'
    ];

    /**
     * @var array
     */
    protected $xsls = [
        'config.xsl',
        'removeFirstTag.xsl',
        'transTagToAttr.xsl'
    ];

    /**
     * @var string
     */
    protected $tagName = 'section';

    /**
     * @var string
     */
    protected $xmlSchema = 'urn:magento:module:Magento_Sales:etc/sales.xsd';
}
