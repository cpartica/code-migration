<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Mapping;

class Context
{
    /**
     * @var string
     */
    protected $m1BaseDir;

    /**
     * @var string
     */
    protected $m2BaseDir;

    /**
     * @return string
     */
    public function getM1BaseDir()
    {
        return $this->m1BaseDir;
    }

    /**
     * @param $m1BaseDir
     * @return $this
     */
    public function setM1BaseDir($m1BaseDir)
    {
        $this->m1BaseDir = $m1BaseDir;
        return $this;
    }

    /**
     * @return string
     */
    public function getM2BaseDir()
    {
        return $this->m2BaseDir;
    }

    /**
     * @param $m2BaseDir
     * @return $this
     */
    public function setM2BaseDir($m2BaseDir)
    {
        $this->m2BaseDir = $m2BaseDir;
        return $this;
    }
}
