<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Mapping;

class ViewMapping
{
    const ADMINHTML = 0;
    const FRONTEND = 1;

    protected $mappingFrontend;

    protected $mappingAdminhtml;

    public function __construct()
    {
        $this->loadAdminhtml();
        $this->loadFrontend();
    }

    protected function loadAdminhtml()
    {
        $mappingFile = BP . '/mapping/view_mapping_adminhtml.json';
        if (file_exists($mappingFile)) {
            $content = file_get_contents($mappingFile);

            $this->mappingAdminhtml = json_decode($content, true);
        } else {
            $this->mappingAdminhtml = [];
        }
    }

    protected function loadFrontend()
    {
        $mappingFile = BP . '/mapping/view_mapping_frontend.json';
        if (file_exists($mappingFile)) {
            $content = file_get_contents($mappingFile);

            $this->mappingFrontend = json_decode($content, true);
        } else {
            $this->mappingFrontend = [];
        }
    }

    public function mapLayoutHandler($handleName, $type = self::ADMINHTML)
    {
        if ($type == self::ADMINHTML) {
            if (isset($this->mappingAdminhtml[$handleName])) {
                return $this->mappingAdminhtml[$handleName];
            }
        } elseif ($type == self::FRONTEND) {
            if (isset($this->mappingFrontend[$handleName])) {
                return $this->mappingFrontend[$handleName];
            }
        }
        return null;
    }
}
