<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\EtcConverter\SystemExtractor;

use \Magento\Migration\Code\EtcConverter\EtcType;
use \Magento\Migration\Code\EtcConverter\ConfigExtractor\ConfigSectionsInterface;

class SystemSections extends EtcType implements ConfigSectionsInterface
{
    const CONFIG_NAME = 'system';

    const FOLDER_NAME = '/adminhtml';

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
        $node = $xmlConfig->getNode();
        if ($node) {
            $fileName = dirname($file) . self::FOLDER_NAME . '/' . self::CONFIG_NAME . '.xml';
            $handlers[] = $this->etcTypeInterfaceFactory->create()
                ->setFileName($fileName)
                ->setXmlContent($node)
                ->setType(self::CONFIG_NAME);
            return $handlers;
        } else {
            return false;
        }

    }
}
