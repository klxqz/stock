<?php

class shopStockPluginModel extends waModel {

    protected $table = 'shop_stock_plugin';

    public function deleteById($id) {
        try {
            $stock_products_model = new shopStockProductsPluginModel();
            $stock_products_model->deleteByField('stock_id', $id);
        } catch (Exception $e) {
            
        }
        parent::deleteById($id);
    }

    public function getStockByProducts($product_ids) {
        $result = array();
        $stocks = $this->getActiveStocks();
        foreach ($stocks as $stock) {
            wa()->getStorage()->set('shop/stockplugin/frontendProductsOff', 1);
            $collection = new shopProductsCollection('stock/' . $stock['id']);
            $stock_products = $collection->getProducts('id,currency', 0, 99999, true);
            wa()->getStorage()->set('shop/stockplugin/frontendProductsOff', 0);
            foreach ($stock_products as $stock_product) {
                if (in_array($stock_product['id'], $product_ids)) {
                    $result[$stock_product['id']] = $stock;
                    if (count($result) == count($product_ids)) {
                        break 2;
                    }
                }
            }
        }
        return $result;
    }

    public function getStockByProductID($product_id) {
        $cache_id = md5('shopStockPlugin::getStockByProductID' . $product_id);
        $cache_time = wa()->getConfig()->isDebug() ? 0 : 7200;
        $cache = new waSerializeCache($cache_id, $cache_time, 'shop/plugins/stock');
        $result = false;
        if ($cache && $cache->isCached()) {
            $result = $cache->get();
        } else {
            $stocks = $this->getActiveStocks();
            foreach ($stocks as $stock) {
                if ($this->stockHasProduct($stock['id'], $product_id)) {
                    $result = $stock;
                    break;
                }
            }
            if ($cache) {
                $cache->set($result);
            }
        }
        return $result;
    }

    public function getStockByCategoryID($category_id) {
        $cache_id = md5('shopStockPlugin::getStockByCategoryID' . $category_id);
        $cache_time = wa()->getConfig()->isDebug() ? 0 : 7200;
        $cache = new waSerializeCache($cache_id, $cache_time, 'shop/plugins/stock');
        $result = false;
        if ($cache && $cache->isCached()) {
            $result = $cache->get();
        } else {
            $stocks = $this->getActiveStocks();
            foreach ($stocks as $stock) {
                if ($this->stockHasCategory($stock['id'], $category_id)) {
                    $result = $stock;
                    break;
                }
            }
            if ($cache) {
                $cache->set($result);
            }
        }
        return $result;
    }

    public function getActiveStocks($sort = 'ASC') {
        $order_by = 'ORDER BY `id` DESC';
        if (strtoupper($sort) == 'ASC') {
            $order_by = 'ORDER BY `id` ASC';
        }
        $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
        $sql = "SELECT * FROM {$this->table} WHERE `datetime_begin` < '" . $now . "' AND `datetime_end` > '" . $now . "' AND `enabled`= 1 " . $order_by;

        $stocks = $this->query($sql)->fetchAll();
        foreach ($stocks as $index => $stock) {
            if (shopStockPlugin::getLastTime($stock) <= 0) {
                unset($stocks[$index]);
            }
        }
        return $stocks;
    }

    private function stockHasProduct($stock_id, $product_id) {
        wa()->getStorage()->set('shop/stockplugin/frontendProductsOff', 1);
        $collection = new shopProductsCollection('stock/' . $stock_id);
        $stock_products = $collection->getProducts('id,currency', 0, 99999, true);
        wa()->getStorage()->set('shop/stockplugin/frontendProductsOff', 0);
        if (isset($stock_products[$product_id])) {
            return true;
        }
        return false;
    }

    private function stockHasCategory($stock_id, $category_id) {
        $stock_products_model = new shopStockProductsPluginModel();
        $stock_products = $stock_products_model->getByField('stock_id', $stock_id, true);
        foreach ($stock_products as $stock_product) {
            if ($stock_product['type'] == 'category' && $stock_product['value'] == $category_id) {
                return true;
            }
        }
        return false;
    }

}
