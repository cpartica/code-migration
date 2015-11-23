<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\AdminhtmlExtractor;


use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class AdminhtmlMenu extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /**
     * @var string
     */
    protected $fileName = 'menu';
    /**
     * @var array
     */
    protected $locations = [
        'menu' => 'adminhtml'
    ];

    /**
     * @var string[]
     */
    protected $xsls = ['config.xsl'];

    /**
     * @var string
     */
    protected $xmlSchema = 'urn:magento:module:Magento_Backend:etc/menu.xsd';
}
