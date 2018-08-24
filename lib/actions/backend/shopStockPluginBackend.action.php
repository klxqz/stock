<?php

class shopStockPluginBackendAction extends waViewAction {

    public function execute() {
        if (($route_hash = waRequest::post('route_hash', null, waRequest::TYPE_STRING)) != '') {
            wa()->getStorage()->write('stock_plugin/route_hash', $route_hash);
        }
        $route_hash = wa()->getStorage()->read('stock_plugin/route_hash');

        $stock_model = new shopStockPluginModel();
        $stocks = $stock_model->getStockByRouteHash($route_hash);
        $stocks = $this->prepareStocks($stocks);
        $this->view->assign(array(
            'stocks' => $stocks,
            'route_hashs' => shopStockHelper::getRouteHashs(),
            'route_hash' => $route_hash,
            'cron_str' => 'php ' . wa()->getConfig()->getRootPath() . '/cli.php shop StockPluginRun',
        ));
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
