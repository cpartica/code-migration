<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\ConfigExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class ConfigSectionsFieldset extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /**
     * @var string
     */
    protected $fileName = 'fieldset';
    /**
     * @var array
     */
    protected $locations = [
        '*/fieldsets' => '.'
    ];

    /**
     * @param \Magento\Framework\Simplexml\Element[] $nodeArray
     * @return \Magento\Framework\Simplexml\Element|null
     */
    protected function mergeNodes($nodeArray)
    {
        if (is_array($nodeArray)) {
            if (!empty($nodeArray)) {
                /** @var \Magento\Framework\Simplexml\Element $node */
                $node = new \Magento\Framework\Simplexml\Element('<config/>');
                for ($index = 0; $index < count($nodeArray); $index++) {
                    if (!$node->xpath('/config/scope[@id=\'' . $nodeArray[$index]->getParent()->getName() . '\']')) {
                        $node->addChild('scope')->addAttribute('id', $nodeArray[$index]->getParent()->getName());
                    }
                    /** @var \Magento\Framework\Simplexml\Element $childNode */
                    $childNode = current(
                        $node->xpath('/config/scope[@id=\'' . $nodeArray[$index]->getParent()->getName() . '\']')
                    );
                    $childNode->appendChild($nodeArray[$index]);
                }
                return $node;
            }
        }
        return null;
    }

    /**
     * @var string[]
     */
    protected $xsls = ['config.xsl'];

    /**
     * @var string
     */
    protected $xmlSchema = 'urn:magento:framework:DataObject/etc/fieldset.xsd';
}
