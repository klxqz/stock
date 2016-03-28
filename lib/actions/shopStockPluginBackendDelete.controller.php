<?php

class shopStockPluginBackendDeleteController extends waJsonController {

    public function execute() {
        try {
            $id = waRequest::post('id', 0, waRequest::TYPE_INT);
            if ($id) {
                $stock_model = new shopStockPluginModel();
                $stock_model->deleteById($id);
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
