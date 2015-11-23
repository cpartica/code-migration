<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\ConfigExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class ConfigSectionsIndex extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /**
     * @var string
     */
    protected $fileName = 'indexer';

    /**
     * @var array
     */
    protected $locations = [
        'global/index/indexer' => '.'
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
    protected $tagName = 'indexer';

    /**
     * @var string
     */
    protected $xmlSchema = 'urn:magento:framework:Indexer/etc/indexer.xsd';
}
