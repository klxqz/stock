<?php

class shopStockPluginBackendAddProductsController extends waJsonController {

    public function execute() {
        try {
            $product_id = waRequest::post('product_id', array(), waRequest::TYPE_ARRAY_INT);
            $stock_id = waRequest::post('stock_id', 0, waRequest::TYPE_INT);
            if (empty($stock_id)) {
                throw new Exception('Не удалось определить акцию');
            }
            if (empty($product_id)) {
                throw new Exception('Не удалось определить товары для добавления к акции');
            }
            $stock_model = new shopStockPluginModel();
            if (!$stock_model->getById($stock_id)) {
                throw new Exception('Не верный идентификатор акции');
            }
            $stock_products_model = new shopStockPluginProductsModel();
            $data = array();
            foreach ($product_id as $value) {
                $data[] = array(
                    'stock_id' => $stock_id,
                    'type' => 'product',
                    'value' => $value,
                );
            }
            $stock_products_model->multiInsert($data);
            $stock_products_model->updateStockProductJoin($stock_id);
            $this->response = 'Товары успешно добавлены';
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
