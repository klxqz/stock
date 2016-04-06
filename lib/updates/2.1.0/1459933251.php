<?php

$model = new waModel();

try {
    $sql = 'SELECT `discount_algorithm` FROM `shop_stock_plugin` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = "ALTER TABLE `shop_stock_plugin` ADD `discount_algorithm` ENUM( 'replace', 'standart' ) NOT NULL DEFAULT 'replace' AFTER `discount_type` ";
    $model->query($sql);
}

try {
    $sql = 'SELECT `badge` FROM `shop_stock_plugin` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = "ALTER TABLE `shop_stock_plugin` ADD `badge` VARCHAR( 32 )";
    $model->query($sql);
}

try {
    $sql = 'SELECT `badge_code` FROM `shop_stock_plugin` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = "ALTER TABLE `shop_stock_plugin` ADD `badge_code` TEXT NOT NULL ";
    $model->query($sql);
}

