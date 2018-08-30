<?php

class shopStockPluginProductsModel extends waModel {

    protected $table = 'shop_stock_plugin_products';

    public function updateStockProductJoin($stock_id) {
        $join_model = new shopStockPluginProductsJoinModel();
        $join_model->deleteByField('stock_id', $stock_id);

        $product_ids = array();
        $stock_products = $this->getByField('stock_id', $stock_id, true);

        foreach ($stock_products as $stock_product) {
            $key = array('stock_id' => $stock_id, 'type' => $stock_product['type'], 'value' => $stock_product['value']);
            switch ($stock_product['type']) {
                case 'product':
                    $product_ids[] = $this->escape($stock_product['value']);
                    $this->updateByField($key, array('count' => 1));
                    break;
                case 'category':
                    $category_collection = new shopProductsCollection('category/' . $stock_product['value']);
                    $products = $category_collection->getProducts('*', 0, 99999, true);
                    if ($products) {
                        $this->updateByField($key, array('count' => count($products)));
                        $product_ids = array_merge($product_ids, array_keys($products));
                    }
                    break;
                case 'type':
                    $type_collection = new shopProductsCollection('type/' . $stock_product['value']);
                    $products = $type_collection->getProducts('*', 0, 99999, true);
                    if ($products) {
                        $this->updateByField($key, array('count' => count($products)));
                        $product_ids = array_merge($product_ids, array_keys($products));
                    }
                    break;
                case 'set':
                    $set_collection = new shopProductsCollection('set/' . $stock_product['value']);
                    $products = $set_collection->getProducts('*', 0, 99999, true);
                    if ($products) {
                        $this->updateByField($key, array('count' => count($products)));
                        $product_ids = array_merge($product_ids, array_keys($products));
                    }
                    break;
                case 'feature':
                    $val = explode(':', $stock_product['value']);
                    $feature_model = new shopFeatureModel();
                    $feature = $feature_model->getById($val[0]);
                    $feature_data = array($feature['code'] => $val[1]);
                    $feature_collection = new shopProductsCollection();
                    $feature_collection->filters($feature_data);
                    $products = $feature_collection->getProducts('*', 0, 99999, true);
                    if ($products) {
                        $this->updateByField($key, array('count' => count($products)));
                        $product_ids = array_merge($product_ids, array_keys($products));
                    }
                    break;
            }
        }

        $data = array();

        foreach ($product_ids as $product_id) {
            $data[] = array(
                'stock_id' => $stock_id,
                'product_id' => $product_id,
            );
        }

        $join_model->multiInsert($data);
    }

}
