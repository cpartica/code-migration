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

$installFile = dirname(__FILE__) . DS . 'upgrade-1.6.0.0.8-1.6.0.0.9.php';
if (file_exists($installFile)) {
    include $installFile;
}

/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
/** @var $connection Varien_Db_Adapter_Pdo_Mysql */
$connection = $installer->getConnection();
$memoryTables = array(
    'catalog/category_anchor_indexer_tmp',
    'catalog/category_anchor_products_indexer_tmp',
    'catalog/category_product_enabled_indexer_tmp',
    'catalog/category_product_indexer_tmp',
    'catalog/product_eav_decimal_indexer_tmp',
    'catalog/product_eav_indexer_tmp',
    'catalog/product_price_indexer_cfg_option_aggregate_tmp',
    'catalog/product_price_indexer_cfg_option_tmp',
    'catalog/product_price_indexer_final_tmp',
    'catalog/product_price_indexer_option_aggregate_tmp',
    'catalog/product_price_indexer_option_tmp',
    'catalog/product_price_indexer_tmp',
);

foreach ($memoryTables as $table) {
    $connection->changeTableEngine($installer->getTable($table), Varien_Db_Adapter_Pdo_Mysql::ENGINE_MEMORY);
}
