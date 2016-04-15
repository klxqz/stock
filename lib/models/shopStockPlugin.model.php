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

    public function getStockByProductID($product_id) {
        $stocks = $this->getActiveStocks();
        foreach ($stocks as $stock) {
            if ($this->stockHasProduct($stock['id'], $product_id)) {
                return $stock;
            }
        }
    }

    public function getStockByCategoryID($category_id) {
        $stocks = $this->getActiveStocks();
        foreach ($stocks as $stock) {
            if ($this->stockHasCategory($stock['id'], $category_id)) {
                return $stock;
            }
        }
    }

    public function getActiveStocks() {
        $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
        $sql = "SELECT * FROM {$this->table} WHERE `datetime_begin` < '" . $now . "' AND `datetime_end` > '" . $now . "' AND `enabled`= 1";
        return $this->query($sql)->fetchAll();
    }

    private function stockHasProduct($stock_id, $product_id) {
        $stock_products_model = new shopStockProductsPluginModel();
        $stock_products = $stock_products_model->getByField('stock_id', $stock_id, true);
        foreach ($stock_products as $stock_product) {
            switch ($stock_product['type']) {
                case 'product':
                    if ($stock_product['value'] == $product_id) {
                        return true;
                    }
                    break;
                case 'category':
                    wa()->getStorage()->set('shop/stockplugin/frontendProductsOff', 1);
                    $collection = new shopProductsCollection('category/' . $stock_product['value']);
                    $products = $collection->getProducts('*', 99999, null, true);
                    wa()->getStorage()->set('shop/stockplugin/frontendProductsOff', 0);
                    if (isset($products[$product_id])) {
                        return true;
                    }
                    break;
                case 'type':
                    $product_model = new shopProductModel();
                    $product = $product_model->getById($product_id);
                    if ($stock_product['value'] == $product['type_id']) {
                        return true;
                    }
                    break;
                case 'set':
                    wa()->getStorage()->set('shop/stockplugin/frontendProductsOff', 1);
                    $collection = new shopProductsCollection('set/' . $stock_product['value']);
                    $products = $collection->getProducts('*', 99999, null, true);
                    wa()->getStorage()->set('shop/stockplugin/frontendProductsOff', 0);
                    if (isset($products[$product_id])) {
                        return true;
                    }
                    break;
            }
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
