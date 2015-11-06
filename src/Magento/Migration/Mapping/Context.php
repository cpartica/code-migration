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
     * @var string
     */
    protected $m1StructureConvertedDir;

    /**
     * @return string
     */
    public function getM1BaseDir()
    {
        return $this->m1BaseDir;
    }

    /**
     * @param string $m1BaseDir
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
     * @param string $m2BaseDir
     * @return $this
     */
    public function setM2BaseDir($m2BaseDir)
    {
        $this->m2BaseDir = $m2BaseDir;
        return $this;
    }

    /**
     * @return string
     */
    public function getm1StructureConvertedDir()
    {
        return $this->m1StructureConvertedDir;
    }

    /**
     * @param string $m1StructureConvertedDir
     * @return $this
     */
    public function setm1StructureConvertedDir($m1StructureConvertedDir)
    {
        $this->m1StructureConvertedDir = $m1StructureConvertedDir;
        return $this;
    }
}
