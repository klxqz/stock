<?php

class shopStockPluginBackendDialogAction extends waViewAction {

    public function execute() {
        $stock_model = new shopStockPluginModel();
        $stocks = $stock_model->getAll();
        $this->view->assign('stocks', $stocks);
    }

}
