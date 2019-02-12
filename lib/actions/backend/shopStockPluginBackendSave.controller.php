<?php

class shopStockPluginBackendSaveController extends waJsonController
{

    public function execute()
    {
        try {
            $stock = waRequest::post('stock');

            if (!empty($stock['datetime_begin'])) {
                $stock['datetime_begin'] = date('Y-m-d H:i', strtotime($stock['datetime_begin']));
            }
            if (!empty($stock['datetime_end'])) {
                $stock['datetime_end'] = date('Y-m-d H:i', strtotime($stock['datetime_end']));
            }

            if (!empty($stock['restart_period'])) {
                $stock['restart_period'] = json_encode($stock['restart_period']);
            } else {
                $stock['restart_period'] = '';
            }

            if (!empty($stock['params'])) {
                $params = array();
                $rows = explode("\n", $stock['params']);
                foreach ($rows as $row) {
                    list($key, $value) = explode("=", $row);
                    $params[trim($key)] = trim($value);
                }
                $stock['params'] = json_encode($params);
            }

            $stock_model = new shopStockPluginModel();

            if (!empty($stock['id'])) {
                $stock_model->updateById($stock['id'], $stock);
            } else {
                $id = $stock_model->insert($stock);
                $stock['id'] = $id;
            }

            if (!$stock['page_url']) {
                $stock['page_url'] = $stock['id'];
                $stock_model->updateById($stock['id'], $stock);
            }

            $stock_storefront = new shopStockPluginStorefrontModel();
            $stock_storefront->deleteByField('stock_id', $stock['id']);
            if (!empty($stock['storefront'])) {
                $storefront = array();
                foreach ($stock['storefront'] as $route_hash) {
                    $storefront[] = array(
                        'stock_id' => $stock['id'],
                        'route_hash' => $route_hash,
                    );
                }
                $stock_storefront->multiInsert($storefront);
            }


            $stock_products = waRequest::post('stock_products');
            $stock_products_model = new shopStockPluginProductsModel();
            $stock_products_model->deletebyField('stock_id', $stock['id']);
            if (!empty($stock_products['value'])) {
                $data_products = array();
                foreach ($stock_products['value'] as $index => $value) {
                    if (empty($stock_products['id'][$index])) {
                        $data_products[] = array(
                            'stock_id' => $stock['id'],
                            'type' => $stock_products['type'][$index],
                            'value' => $value
                        );
                    }
                }
                $stock_products_model->multiInsert($data_products);
            }
            $stock_products_model->updateStockProductJoin($stock['id']);


            if ($stock = $this->checkStockProducts($stock['id'])) {
                $stock_model->deleteById($stock['id']);
                throw new waException(sprintf("Обнаружено пересечение товаров с акцией: «%s»", $stock['name']));
            }

            $this->response = 'Сохранено';
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

    private function checkStockProducts($stock_id)
    {
        $stock_model = new shopStockPluginModel();
        $collection = new shopProductsCollection('stock/' . $stock_id);
        $check_products = $collection->getProducts('*', 0, 99999, true);
        $check_product_ids = array_keys($check_products);

        $stocks = $stock_model->getAll();
        foreach ($stocks as $stock) {
            if ($stock['id'] != $stock_id) {
                $collection = new shopProductsCollection('stock/' . $stock['id']);
                $products = $collection->getProducts('*', 0, 99999, true);
                $product_ids = array_keys($products);
                if (array_intersect($check_product_ids, $product_ids)) {
                    return $stock;
                }
            }
        }
        return 0;
    }

}
