<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\ConfigExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class ConfigSectionsEmailTemplate extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /**
     * @var string
     */
    protected $fileName = 'email_templates';

    /**
     * @var array
     */
    protected $locations = [
        'global/template/email' => '.'
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
    protected $tagName = 'template';

    /**
     * @var string
     */
    protected $xmlSchema = 'urn:magento:module:Magento_Email:etc/email_templates.xsd';
}
