<?php

$model = new waModel();

try {
    $sql = 'SELECT `rounding` FROM `shop_stock_plugin` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = "ALTER TABLE `shop_stock_plugin` ADD `rounding` TINYINT( 1 ) NOT NULL DEFAULT '1'";
    $model->query($sql);
}

try {
    $sql = 'SELECT `multiple_badges` FROM `shop_stock_plugin` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = "ALTER TABLE `shop_stock_plugin` ADD `multiple_badges` TINYINT( 1 ) NOT NULL DEFAULT '0'";
    $model->query($sql);
}

