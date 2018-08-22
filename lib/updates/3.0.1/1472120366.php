<?php

$model = new waModel();

try {
    $sql = "ALTER TABLE `shop_stock_plugin` ADD `begin_time` TIME NOT NULL AFTER `restart`";
    $model->query($sql);
} catch (waDbException $ex) {
    
}