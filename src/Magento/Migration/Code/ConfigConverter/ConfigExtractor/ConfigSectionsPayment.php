<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\ConfigExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class ConfigSectionsPayment extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /**
     * @var string
     */
    protected $fileName = 'payment';
    /**
     * @var array
     */
    protected $locations = [
        'global/payment' => '.'
    ];

    /**
     * @var string[]
     */
    protected $xsls = ['config.xsl'];

    /**
     * @var string
     */
    protected $xmlSchema = 'urn:magento:module:Magento_Payment:etc/payment.xsd';
}
