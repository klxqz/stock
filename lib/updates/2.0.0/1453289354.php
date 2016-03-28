<?php

$model = new waModel();

try {
    $sql = "DROP TABLE `shop_stockplugin`";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `shop_stock_plugin` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `enabled` tinyint(1) NOT NULL DEFAULT '1',
      `name` varchar(255) NOT NULL DEFAULT '',
      `description` text NOT NULL,
      `homepage` tinyint(1) NOT NULL DEFAULT '0',
      `datetime_begin` datetime NOT NULL,
      `datetime_end` datetime NOT NULL,
      `type` enum('discount','gift') NOT NULL DEFAULT 'discount',
      `discount_type` enum('percent','absolute','price') NOT NULL DEFAULT 'percent',
      `discount_value` decimal(15,4) NOT NULL DEFAULT '0.0000',
      `gift_sku_id` int(11) NOT NULL DEFAULT '0',
      `page_name` varchar(255) NOT NULL DEFAULT '',
      `page_url` varchar(255) NOT NULL DEFAULT '',
      `page_title` varchar(255) NOT NULL DEFAULT '',
      `meta_keywords` varchar(255) NOT NULL DEFAULT '',
      `meta_description` varchar(255) NOT NULL DEFAULT '',
      `page_content` text NOT NULL,
      PRIMARY KEY (`id`),
      KEY `datetime_begin` (`datetime_begin`),
      KEY `datetime_end` (`datetime_end`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `shop_stock_products_plugin` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `stock_id` int(11) NOT NULL,
    `type` enum('product','set','type','category') NOT NULL DEFAULT 'product',
    `value` varchar(64) NOT NULL NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `stock_id` (`stock_id`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $files = array(
        'plugins/stock/templates/StockInfo.html',
        'plugins/stock/lib/classes/shopStockProductsCollection.class.php',
        'plugins/stock/lib/actions/shopStockPluginBackendSavesettings.controller.php',
        'plugins/stock/lib/config/routing.php',
        'plugins/stock/js/loadcontent.js',
        'plugins/stock/templates/FrontendNav.html',
        'plugins/stock/templates/BackendProduct.html',
        'plugins/stock/templates/FrontendProduct.html',
        'plugins/stock/img/countdown/flipper92.png',
        'plugins/stock/img/countdown/flipper91.png',
        'plugins/stock/img/countdown/flipper90.png',
        'plugins/stock/img/countdown/flipper82.png',
        'plugins/stock/img/countdown/flipper81.png',
        'plugins/stock/img/countdown/flipper80.png',
        'plugins/stock/img/countdown/flipper72.png',
        'plugins/stock/img/countdown/flipper71.png',
        'plugins/stock/img/countdown/flipper70.png',
        'plugins/stock/img/countdown/flipper62.png',
        'plugins/stock/img/countdown/flipper61.png',
        'plugins/stock/img/countdown/flipper60.png',
        'plugins/stock/img/countdown/flipper52.png',
        'plugins/stock/img/countdown/flipper51.png',
        'plugins/stock/img/countdown/flipper50.png',
        'plugins/stock/img/countdown/flipper42.png',
        'plugins/stock/img/countdown/flipper41.png',
        'plugins/stock/img/countdown/flipper40.png',
        'plugins/stock/img/countdown/flipper32.png',
        'plugins/stock/img/countdown/flipper31.png',
        'plugins/stock/img/countdown/flipper30.png',
        'plugins/stock/img/countdown/flipper22.png',
        'plugins/stock/img/countdown/flipper21.png',
        'plugins/stock/img/countdown/flipper20.png',
        'plugins/stock/img/countdown/flipper12.png',
        'plugins/stock/img/countdown/flipper11.png',
        'plugins/stock/img/countdown/flipper10.png',
        'plugins/stock/img/countdown/flipper02.png',
        'plugins/stock/img/countdown/flipper01.png',
        'plugins/stock/img/countdown/flipper00.png',
        'plugins/stock/js/countdown.js',
    );

    foreach ($files as $file) {
        waFiles::delete(wa()->getAppPath($file, 'shop'), true);
    }
} catch (Exception $e) {
    
}

$plugin_id = array('shop', 'stock');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'stock_page', '1');
$app_settings_model->set($plugin_id, 'page_url', 'stock/');
$app_settings_model->set($plugin_id, 'page_name', 'Акции');
$app_settings_model->set($plugin_id, 'page_title', '');
$app_settings_model->set($plugin_id, 'meta_keywords', '');
$app_settings_model->set($plugin_id, 'meta_description', '');
$app_settings_model->set($plugin_id, 'page_template', 'page.html');
$app_settings_model->set($plugin_id, 'stock_page_template', 'search.html');
$app_settings_model->set($plugin_id, 'cart_products_template', 'list-thumbs.html');
$app_settings_model->set($plugin_id, 'frontend_product_output', 'block_aux');
$app_settings_model->set($plugin_id, 'frontend_category_output', '1');
$app_settings_model->set($plugin_id, 'countdown_range_hi', 'day');
$app_settings_model->set($plugin_id, 'countdown_width', '');
$app_settings_model->set($plugin_id, 'countdown_height', '');
$app_settings_model->set($plugin_id, 'countdown_style', 'boring');
$app_settings_model->set($plugin_id, 'countdown_hide_line', 'false');
$app_settings_model->set($plugin_id, 'countdown_hide_labels', 'false');
$app_settings_model->set($plugin_id, 'countdown_hide_labels', 'false');
$app_settings_model->set($plugin_id, 'countdown_second_text', 'Секунды');
$app_settings_model->set($plugin_id, 'countdown_minute_text', 'Минуты');
$app_settings_model->set($plugin_id, 'countdown_hour_text', 'Часы');
$app_settings_model->set($plugin_id, 'countdown_day_text', 'Дни');
$app_settings_model->set($plugin_id, 'countdown_month_text', 'Месяцы');
$app_settings_model->set($plugin_id, 'countdown_year_text', 'Годы');
$app_settings_model->set($plugin_id, 'countdown_numbers_color', '#FFFFFF');
$app_settings_model->set($plugin_id, 'countdown_numbers_bkgd', '#365D8B');
$app_settings_model->set($plugin_id, 'countdown_labels_color', '#000000');
$app_settings_model->set($plugin_id, 'countdown_labels_size', '0.7');
