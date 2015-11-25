<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\ConfigExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigSectionsInterface;
use \Magento\Migration\Code\ConfigConverter\ConfigSectionsAbstract;

class ConfigSectionsRouters extends ConfigSectionsAbstract implements ConfigSectionsInterface
{
    /**
     * @var string
     */
    protected $fileName = 'routes';
    /**
     * @var array
     */
    protected $locations = [
        'global/routers' => '.',
        'frontend/routers' => 'frontend',
        'adminhtml/routers' => 'adminhtml',
        'admin/routers' => 'adminhtml',
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
                    if (!$node->xpath('/config/router[@id=\'' . $nodeArray[$index]->getParent()->getName() . '\']')) {
                        $node->addChild('router')->addAttribute('id', $nodeArray[$index]->getParent()->getName());
                    }
                    /** @var \Magento\Framework\Simplexml\Element $childNode */
                    $childNode = current(
                        $node->xpath('/config/router[@id=\'' . $nodeArray[$index]->getParent()->getName() . '\']')
                    );
                    $childNode->appendChild($nodeArray[$index]->children()[0]);
                }
                $node->children()[0]->setAttribute(
                    'id',
                    preg_match('/admin/is', $node->children()[0]->getAttribute('id')) ? 'admin': 'standard'
                );
                return $node;
            }
        }
        return null;
    }

    /**
     * @var string[]
     */
    protected $xsls = [
        'config.xsl',
        'removeFirstTag.xsl',
        'transTagToAttr2ndLvl.xsl',
        'routeArgsToParentAsAttr.xsl'
    ];

    /**
     * @var string
     */
    protected $tagName = 'route';

    /**
     * @var string
     */
    protected $xmlSchema = 'urn:magento:framework:App/etc/routes.xsd';
}
