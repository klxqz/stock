<?php

class shopStockPluginModel extends waModel {

    protected $table = 'shop_stockplugin';

    public function getActiveStocks($key = null, $normalize = false) {
        $now = waDateTime::date("Y-m-d", null, wa()->getUser()->getTimezone());
        $sql = "SELECT * FROM {$this->table} WHERE `date_begin` <= '" . $now . "' AND `date_end` >= '" . $now . "'";
        return $this->query($sql)->fetchAll($key, $normalize);
    }

    public function getActiveStockByProduct($product_id) {
        $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
        $sql = "SELECT * FROM {$this->table} WHERE `date_begin` < '" . $now . "' AND `date_end` > '" . $now . "' AND `product_id`='" . (int) $product_id . "'";
        return $this->query($sql)->fetch();
    }

}
