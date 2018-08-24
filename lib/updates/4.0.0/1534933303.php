<?php

$model = new waModel();

try {
    $sql = "RENAME TABLE `shop_stock_products_plugin` TO `shop_stock_plugin_products`;";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "ALTER TABLE `shop_stock_plugin_products` DROP `id`;";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "ALTER TABLE `shop_stock_plugin_products` ADD `count` INT( 11 ) NOT NULL DEFAULT '0';";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "ALTER TABLE `shop_stock_plugin` ADD `params` TEXT NOT NULL AFTER `page_content`;";
    $model->query($sql);
} catch (waDbException $ex) {
    
}



try {
    $sql = "CREATE TABLE IF NOT EXISTS `shop_stock_plugin_products_join` (
      `stock_id` int(11) NOT NULL,
      `product_id` int(11) NOT NULL,
      KEY `stock_id` (`stock_id`),
      KEY `product_id` (`product_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `shop_stock_plugin_storefront` (
      `stock_id` int(11) NOT NULL,
      `route_hash` varchar(32) NOT NULL DEFAULT '',
      KEY `stock_id` (`stock_id`),
      KEY `route_hash` (`route_hash`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    $model->query($sql);
} catch (waDbException $ex) {
    
}


$files = array(
    'plugins/stock/lib/actions/shopStockPluginBackend.action.php',
    'plugins/stock/lib/actions/shopStockPluginBackendAddProducts.controller.php',
    'plugins/stock/lib/actions/shopStockPluginBackendDelete.controller.php',
    'plugins/stock/lib/actions/shopStockPluginBackendDeleteStockImage.controller.php',
    'plugins/stock/lib/actions/shopStockPluginBackendDeleteStockProducts.controller.php',
    'plugins/stock/lib/actions/shopStockPluginBackendDialog.action.php',
    'plugins/stock/lib/actions/shopStockPluginBackendSave.controller.php',
    'plugins/stock/lib/actions/shopStockPluginBackendSkuAutocomplete.controller.php',
    'plugins/stock/lib/actions/shopStockPluginBackendStock.action.php',
    'plugins/stock/lib/actions/shopStockPluginBackendUpload.controller.php',
    'plugins/stock/lib/actions/shopStockPluginFrontendStock.action.php',
    'plugins/stock/lib/actions/shopStockPluginFrontendStockList.action.php',
    'plugins/stock/lib/actions/shopStockPluginSettings.action.php',
    'plugins/stock/lib/actions/shopStockPluginSettingsSave.controller.php',
    'plugins/stock/lib/models/shopStockProductsPlugin.model.php',
);

foreach ($files as $file) {
    try {
        waFiles::delete(wa()->getAppPath($file, 'shop'), true);
    } catch (Exception $e) {
        
    }
}