<?php

class shopStockPluginBackendAction extends waViewAction {

    public function execute() {
        $id = waRequest::get('id', null, waRequest::TYPE_INT);

        $product_model = new shopProductModel();
        $product = $product_model->getById($id);
        if (!$product) {
            throw new waException(_w("Unknown product"));
        }
        
        $this->view->assign('product', $product);
    }

}
