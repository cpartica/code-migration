<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$this->startSetup()->run("

ALTER TABLE {$this->getTable('catalog_product_entity_tier_price')}
    ADD COLUMN `customer_group_id` smallint(5) unsigned NOT NULL default '0' AFTER `entity_id`,
    ADD CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_TIER_PRICE_GROUP` FOREIGN KEY (`customer_group_id`)
    REFERENCES {$this->getTable('customer_group')} (`customer_group_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

update {$this->getTable('catalog_product_entity_tier_price')} set `customer_group_id`=(select `customer_group_id` from {$this->getTable('customer_group')} limit 1);

")->endSetup();
