<?php

class shopStockPluginModel extends waModel {

    protected $table = 'shop_stock_plugin';

    public function deleteById($id) {
        try {
            $stock_products_model = new shopStockPluginProductsModel();
            $stock_products_model->deleteByField('stock_id', $id);

            $stock_storefront = new shopStockPluginStorefrontModel();
            $stock_storefront->deleteByField('stock_id', $id);
        } catch (Exception $e) {
            
        }
        parent::deleteById($id);
    }

    public function getStockByRouteHash($route_hash = null) {
        $sql = "SELECT * FROM {$this->table}"
                . ($route_hash ? " WHERE `id` IN (
                        SELECT `stock_id` 
                        FROM `shop_stock_plugin_storefront` 
                        WHERE `route_hash`='" . $this->escape($route_hash) . "'
                )" : '');

        return $this->query($sql)->fetchAll();
    }

    public function getStockByProducts($product_ids) {
        $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
        $sql = "SELECT `s`.*, `j`.`product_id`
                FROM {$this->table} as `s`
                LEFT JOIN `shop_stock_plugin_products_join` as `j`
                ON `s`.`id` = `j`.`stock_id` AND `j`.`product_id` IN ('" . implode("','", $product_ids) . "')
                WHERE 
                `s`.`datetime_begin` < '" . $now . "' AND
                `s`.`datetime_end` > '" . $now . "' AND 
                `s`.`enabled`= 1 AND
                `s`.`id` IN (
                        SELECT `stock_id` 
                        FROM `shop_stock_plugin_storefront` 
                        WHERE `route_hash` = 0 OR 
                        `route_hash`='" . shopStockHelper::getCurrentRouteHash() . "'
                )";

        $stocks = $this->query($sql)->fetchAll('product_id');

        foreach ($stocks as $index => $stock) {
            $stock['params'] = json_decode($stock['params'], true);
            if (shopStockHelper::getLastTime($stock) <= 0) {
                unset($stocks[$index]);
            }
        }
        return $stocks;
    }

    public function getStockByProductID($product_id) {
        $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
        $sql = "SELECT * FROM {$this->table}
                WHERE 
                `datetime_begin` < '" . $now . "' AND
                `datetime_end` > '" . $now . "' AND 
                `enabled`= 1 AND
                `id` IN (
                        SELECT `stock_id` 
                        FROM `shop_stock_plugin_storefront` 
                        WHERE `route_hash` = 0 OR 
                        `route_hash`='" . shopStockHelper::getCurrentRouteHash() . "'
                ) AND
                `id` IN (
                        SELECT `stock_id` 
                        FROM `shop_stock_plugin_products_join` 
                        WHERE `product_id`='" . (int) $product_id . "'
                )";

        $stock = $this->query($sql)->fetchAssoc();
        $stock['params'] = json_decode($stock['params'], true);
        if (shopStockHelper::getLastTime($stock) <= 0) {
            return false;
        }
        return $stock;
    }

    public function getStockByCategoryID($category_id) {
        $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
        $sql = "SELECT * FROM {$this->table}
                WHERE 
                `datetime_begin` < '" . $now . "' AND
                `datetime_end` > '" . $now . "' AND 
                `enabled`= 1 AND
                `id` IN (
                        SELECT `stock_id` 
                        FROM `shop_stock_plugin_storefront` 
                        WHERE `route_hash` = 0 OR 
                        `route_hash`='" . shopStockHelper::getCurrentRouteHash() . "'
                ) AND
                `id` IN (
                        SELECT `stock_id` 
                        FROM `shop_stock_plugin_products` 
                        WHERE `type` = 'category' AND `value` = '" . (int) $category_id . "'
                )";

        $stock = $this->query($sql)->fetchAssoc();
        $stock['params'] = json_decode($stock['params'], true);
        if (shopStockHelper::getLastTime($stock) <= 0) {
            return false;
        }
        return $stock;
    }

    public function getActiveStocks($sort = 'ASC') {
        if (strtoupper($sort) != 'ASC') {
            $sort = 'DESC';
        }
        $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
        $sql = "SELECT * FROM {$this->table}
                WHERE 
                `datetime_begin` < '" . $now . "' AND
                `datetime_end` > '" . $now . "' AND 
                `enabled`= 1 AND
                `id` IN (
                        SELECT `stock_id` 
                        FROM `shop_stock_plugin_storefront` 
                        WHERE `route_hash` = 0 OR 
                        `route_hash`='" . shopStockHelper::getCurrentRouteHash() . "'
                )
                ORDER BY `id`" . $sort;

        $stocks = $this->query($sql)->fetchAll();
        foreach ($stocks as $index => $stock) {
            $stock['params'] = json_decode($stock['params'], true);
            if (shopStockHelper::getLastTime($stock) <= 0) {
                unset($stocks[$index]);
            }
        }
        return $stocks;
    }

    public function getActiveStockByUrl($url) {
        $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
        $sql = "SELECT * FROM {$this->table}
                WHERE 
                `datetime_begin` < '" . $now . "' AND
                `datetime_end` > '" . $now . "' AND 
                `enabled`= 1 AND
                `id` IN (
                        SELECT `stock_id` 
                        FROM `shop_stock_plugin_storefront` 
                        WHERE `route_hash` = 0 OR 
                        `route_hash`='" . shopStockHelper::getCurrentRouteHash() . "'
                ) AND
                `page_url` = '" . $this->escape($url) . "'";

        $stock = $this->query($sql)->fetchAssoc();
        $stock['params'] = json_decode($stock['params'], true);
        if (shopStockHelper::getLastTime($stock) <= 0) {
            return false;
        }
        return $stock;
    }

}
