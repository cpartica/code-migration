<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter\AdminhtmlExtractor;

use \Magento\Migration\Code\ConfigConverter\ConfigType;
use \Magento\Migration\Code\ConfigConverter\ConfigExtractor\ConfigSectionsInterface;

class AdminhtmlMenu extends ConfigType implements ConfigSectionsInterface
{
    const CONFIG_NAME = 'menu';

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
        $node = $xmlConfig->getNode(self::CONFIG_NAME);
        if ($node) {
            $fileName = dirname($file) . \DIRECTORY_SEPARATOR . self::FOLDER_NAME . DIRECTORY_SEPARATOR .
                self::CONFIG_NAME . '.xml';
            $handlers[] = $this->configTypeInterfaceFactory->create()
                ->setFileName($fileName)
                ->setXmlContent($node)
                ->setType(self::CONFIG_NAME);
            return $handlers;
        } else {
            return false;
        }

    }
}
