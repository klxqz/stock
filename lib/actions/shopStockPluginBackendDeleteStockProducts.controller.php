<?php

class shopStockPluginBackendDeleteStockProductsController extends waJsonController {

    public function execute() {
        try {
            $id = waRequest::post('id', 0, waRequest::TYPE_INT);
            if ($id) {
                $stock_products_model = new shopStockProductsPluginModel();
                $stock_products_model->deleteById($id);
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
