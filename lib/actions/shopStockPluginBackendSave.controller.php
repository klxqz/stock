<?php

class shopStockPluginBackendSaveController extends waJsonController {

    public function execute() {
        try {
            $stock = waRequest::post('stock');
            
            if (!empty($stock['datetime_begin'])) {
                $stock['datetime_begin'] = date('Y-m-d H:i', strtotime($stock['datetime_begin']));
            }
            if (!empty($stock['datetime_end'])) {
                $stock['datetime_end'] = date('Y-m-d H:i', strtotime($stock['datetime_end']));
            }
            
            $stock_model = new shopStockPluginModel();
            if (!empty($stock['id'])) {
                $stock_model->updateById($stock['id'], $stock);
            } else {
                $id = $stock_model->insert($stock);
                $stock['id'] = $id;
            }
            
            if(!$stock['page_url']) {
                $stock['page_url'] = $stock['id'];
                $stock_model->updateById($stock['id'], $stock);
            }

            $stock_products = waRequest::post('stock_products');
            if (!empty($stock_products['value'])) {
                $stock_products_model = new shopStockProductsPluginModel();
                foreach ($stock_products['value'] as $index => $value) {
                    if(empty($stock_products['id'][$index])) {
                        $data = array(
                            'stock_id' => $stock['id'],
                            'type' => $stock_products['type'][$index],
                            'value' => $value,
                        );
                        $stock_products_model->insert($data);
                    }
                }
            }

            $this->response = 'Сохранено';
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
