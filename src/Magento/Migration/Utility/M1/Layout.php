<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Utility\M1;

class Layout
{
    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var \SimpleXMLElement
     */
    protected $config;

    /**
     * @param string $configFileContent
     */
    public function __construct($configFileContent)
    {
        $this->config = simplexml_load_string($configFileContent);
    }

    /**
     * @return array
     */
    public function getLayoutHandlers()
    {
        $layoutHandlers = [];
        $handlers = $this->config->xpath('/layout/*');
        if (is_array($handlers)) {
            foreach ($handlers as $tableAlias) {
                /** @var \SimpleXMLElement $tableAlias */
                $layoutHandlers[$tableAlias->getName()] = $tableAlias->getName();
            }
        }
        return $layoutHandlers;
    }
}
