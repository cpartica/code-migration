<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\EtcConverter;

interface EtcExtractorInterface
{
    /**
     * loops through layout handlers in an M1 layout xml format
     *
     * @return array|null
     */
    public function getEtcTypes();

    /**
     * @param  string $etcFile
     * @return $this
     */
    public function setFile($etcFile);
}
