<?php

$model = new waModel();

try {
    $sql = "ALTER TABLE `shop_stock_plugin` CHANGE `restart_period` `restart_period` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

