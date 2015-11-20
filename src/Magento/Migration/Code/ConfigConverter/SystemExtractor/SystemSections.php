<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\SystemExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigType;
use \Magento\Migration\Code\ConfigConverter\ConfigExtractor\ConfigSectionsInterface;

class SystemSections extends ConfigType implements ConfigSectionsInterface
{
    const CONFIG_NAME = 'system';

    const FOLDER_NAME = '/adminhtml';

    /**
     * @var \Magento\Migration\Code\ConfigConverter\ConfigTypeInterface
     */
    protected $configTypeInterfaceFactory;

    /**
     * @param \Magento\Migration\Code\ConfigConverter\ConfigType $configTypeInterfaceFactory
     */
    public function __construct(
        \Magento\Migration\Code\ConfigConverter\ConfigTypeFactory $configTypeInterfaceFactory
    ) {
        $this->configTypeInterfaceFactory = $configTypeInterfaceFactory;
    }

    /**
     * @param string $file
     * @param \Magento\Framework\Simplexml\Config $xmlConfig
     * @return \Magento\Migration\Code\ConfigConverter\ConfigTypeInterface[]|false
     */
    public function extract($file, $xmlConfig)
    {
        $handlers = [];
        if ($xmlConfig) {
            $node = $xmlConfig->getNode();
            if ($node) {
                $fileName = dirname($file) . self::FOLDER_NAME . '/' . self::CONFIG_NAME . '.xml';
                $handlers[] = $this->configTypeInterfaceFactory->create()
                    ->setFileName($fileName)
                    ->setXmlContent($node)
                    ->setType(self::CONFIG_NAME);
                return $handlers;
            }
        }
        return false;

    }
}
