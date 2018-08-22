<?php

$model = new waModel();

try {
    $sql = "ALTER TABLE `shop_stock_products_plugin` CHANGE `type` `type` ENUM( 'product', 'set', 'category', 'type', 'feature' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'product'";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "ALTER TABLE `shop_stock_plugin` ADD `img` VARCHAR( 255 ) NOT NULL AFTER `name`";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "ALTER TABLE `shop_stock_plugin` ADD `promocode_only` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `discount_type`,
            ADD `promocode` VARCHAR( 32 ) NOT NULL AFTER `promocode_only`";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "ALTER TABLE `shop_stock_plugin` CHANGE `type` `type` ENUM( 'discount', 'gift', 'bonus' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'discount'";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "ALTER TABLE `shop_stock_plugin` ADD `absolute_bonus` INT NOT NULL DEFAULT '0' AFTER `gift_sku_id`,
            ADD `product_percent_bonus` INT NOT NULL DEFAULT '0' AFTER `absolute_bonus` ";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "ALTER TABLE `shop_stock_plugin` ADD `restart` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `datetime_end` ,
            ADD `duration_time` INT NOT NULL DEFAULT '0' AFTER `restart` ,
            ADD `restart_period` TEXT NOT NULL AFTER `duration_time` ";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "ALTER TABLE `shop_stock_plugin` ADD `period_begin` DATETIME NOT NULL AFTER `restart_period` ,
            ADD `period_end` DATETIME NOT NULL AFTER `period_begin` ,
            ADD `period_runing` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `period_end`";
    $model->query($sql);
} catch (waDbException $ex) {
    
}