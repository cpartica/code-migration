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


$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('catalog/product'), 'required_options', 'tinyint(1) unsigned NOT NULL default 0');

$entityTypeId   = 'catalog_product';
$attributeId    = $installer->getAttributeId($entityTypeId, 'required_options');
$attributeTable = $installer->getAttributeTable($entityTypeId, $attributeId);

if ($attributeTable != $installer->getTable('catalog/product')) {
    $installer->run("
    UPDATE `{$installer->getTable('catalog/product')}` AS `p`
    INNER JOIN `{$attributeTable}` AS `a` ON `p`.`entity_id`=`a`.`entity_id`
        AND `a`.`attribute_id`={$attributeId} AND `a`.`store_id`=0
    SET `p`.`required_options` = `a`.`value`;
    ");

    $installer->updateAttribute($entityTypeId, $attributeId, 'backend_type', 'static');

    $installer->run("
    DELETE FROM `{$attributeTable}` WHERE `attribute_id`={$attributeId};
    ");
}

$installer->endSetup();
