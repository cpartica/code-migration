<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\EtcConverter\ConfigExtractor;

use \Magento\Migration\Code\EtcConverter\EtcType;

class ConfigSectionsEvents implements ConfigSectionsInterface
{
    const CONFIG_NAME = 'events';

    /**
     * @var array
     */
    protected $locations = [
        'global' => '',
        'frontend' => '/frontend',
        'adminhtml' => '/adminhtml',
        'admin' => '/adminhtml',
    ];

    /**
     * @var \Magento\Migration\Code\EtcConverter\EtcTypeInterface
     */
    protected $etcTypeInterfaceFactory;

    /**
     * @param \Magento\Migration\Code\EtcConverter\EtcType $etcTypeInterfaceFactory
     */
    public function __construct(
        \Magento\Migration\Code\EtcConverter\EtcTypeFactory $etcTypeInterfaceFactory
    ) {
        $this->etcTypeInterfaceFactory = $etcTypeInterfaceFactory;
    }

    /**
     * @param string $file
     * @param \Magento\Framework\Simplexml\Config $xmlConfig
     * @return \Magento\Migration\Code\EtcConverter\EtcTypeInterface[]|false
     */
    public function extract($file, $xmlConfig)
    {
        $handlers = [];
        foreach ($this->locations as $location => $folder) {
            $node = $xmlConfig->getNode($location . '/events');
            if ($node) {
                $fileName = dirname($file) . $folder . '/' . self::CONFIG_NAME . '.xml';
                $handlers[] = $this->etcTypeInterfaceFactory->create()
                    ->setFileName($fileName)
                    ->setXmlContent($node)
                    ->setType(self::CONFIG_NAME);
            }
        }
        if (count($handlers)>0) {
            return $handlers;
        }
        return false;
    }
}
