<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;


class DiVariablesPersistent
{
    /** @var array */
    protected $diVariables;

    /**
     * @return array
     */
    public function getDiVariables()
    {
        return $this->diVariables;
    }

    /**
     * @param array $diVariables
     * @return $this
     */
    public function setDiVariables($diVariables)
    {
        $this->diVariables = $diVariables;
        return $this;
    }
}
