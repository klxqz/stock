<?php

class shopStockPluginBackendUpdateController extends waJsonController {

    public function execute() {
        try {
            $id = waRequest::post('id', 0, waRequest::TYPE_INT);

            $stock_products_model = new shopStockPluginProductsModel();
            if ($id) {
                $stock_products_model->updateStockProductJoin($id);
            } else {
                $stock_model = new shopStockPluginModel();
                $stocks = $stock_model->getAll();
                foreach ($stocks as $stock) {
                    $stock_products_model->updateStockProductJoin($stock['id']);
                }
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
