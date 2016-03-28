<?php

class shopStockPluginBackendAction extends waViewAction {

    public function execute() {
        $stock_model = new shopStockPluginModel();
        $stocks = $stock_model->getAll();
        $stocks = $this->prepareStocks($stocks);
        $this->view->assign('stocks', $stocks);
        $this->view->assign('frontend_url', wa()->getRouteUrl('shop/frontend'));
        $app_settings_model = new waAppSettingsModel();
        $page_url = $app_settings_model->get(shopStockPlugin::$plugin_id, 'page_url');
        $this->view->assign('page_url', $page_url);
    }

    private function prepareStocks(&$stocks) {
        foreach ($stocks as &$stock) {
            if ($stock['type'] == 'gift' && !empty($stock['gift_sku_id'])) {
                $sku_model = new shopProductSkusModel();
                $gift_sku = $sku_model->getById($stock['gift_sku_id']);
                if (!empty($gift_sku)) {
                    $product_model = new shopProductModel();
                    $gift_product = $product_model->getById($gift_sku['product_id']);
                    $gift_product['sku'] = $gift_sku;
                    $stock['gift_product'] = $gift_product;
                }
            }
        }
        return $stocks;
    }

}
