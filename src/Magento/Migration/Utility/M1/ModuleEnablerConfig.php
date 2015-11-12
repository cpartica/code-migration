<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Utility\M1;

class ModuleEnablerConfig
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var array
     */
    protected $xmlFiles;

    /**
     * @var \SimpleXMLElement
     */
    protected $config;

    /**
     * @param string $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->grabAllModulesEnablerConfig();
        $this->mergeXMLFiles();
    }

    /**
     * get all the xmls
     * @return void
     */
    protected function grabAllModulesEnablerConfig()
    {
        if (file_exists($this->basePath)) {
            $this->xmlFiles = glob("{$this->basePath}/app/etc/modules/*.xml", GLOB_NOSORT | GLOB_BRACE);
        }
    }

    /**
     * merges all the xmls
     * @return void
     */
    protected function mergeXMLFiles()
    {
        if (is_array($this->xmlFiles)) {
            $base = simplexml_load_string('<?xml version="1.0"?><root></root>');
            foreach ($this->xmlFiles as $xmlFile) {
                $this->mergeXML($base, simplexml_load_file($xmlFile));
            }
        }
        $this->config = $base;
    }

    /**
     * @param \SimpleXMLElement $base
     * @param \SimpleXMLElement $add
     * @return void
     * */
    protected function mergeXML(&$base, $add)
    {
        if ($add->count() != 0) {
            $new = $base->addChild($add->getName());
        } else {
            $new = $base->addChild($add->getName(), $add);
        }
        foreach ($add->attributes() as $a => $b) {
            $new->addAttribute($a, $b);
        }
        if ($add->count() != 0) {
            foreach ($add->children() as $child) {
                $this->mergeXML($new, $child);
            }
        }
    }

    /**
     * @param string $module
     * @param string $pool
     * @return bool
     */
    public function isModuleEnabled($module, $pool)
    {
        if ($this->config) {
            $active = $this->config->xpath("//config/modules/{$module}/active[contains(text(),'true')]");
            $pool = $this->config->xpath("//config/modules/{$module}/codePool[contains(text(),'{$pool}')]");
            return $active && $pool;
        }
        return false;
    }
}
