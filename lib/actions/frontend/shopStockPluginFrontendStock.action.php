<?php

class shopStockPluginFrontendStockAction extends shopFrontendAction {

    public function execute() {
        if (!($route_settings = shopStockPlugin::isEnabled($route_hash))) {
            throw new waException(_ws("Page not found"), 404);
        }

        $page_url = waRequest::param('stock');
        $stock_model = new shopStockPluginModel();
        $stock = $stock_model->getActiveStockByUrl($page_url);
        if (!$stock) {
            throw new waException(_ws("Page not found"), 404);
        }
        $time = shopStockHelper::getLastTime($stock);
        if ($time <= 0) {
            throw new waException(_ws("Page not found"), 404);
        }

        $this->getResponse()->setTitle($stock['page_title']);
        $this->getResponse()->setMeta('keywords', $stock['meta_keywords']);
        $this->getResponse()->setMeta('description', $stock['meta_description']);

        $collection = new shopProductsCollection('stock/' . $stock['id']);
        $this->setCollection($collection);

        $this->view->assign(array(
            'stock' => $stock,
            'time' => $time,
            'settings' => $route_settings,
        ));
        $frontend_stock_template = shopStockHelper::getRouteTemplates($route_hash, 'FrontendStock', false);
        $html = $this->view->fetch($frontend_stock_template['template_path']);

        $this->view->assign(array(
            'frontend_search' => array('stock' => $html) + wa()->event('frontend_search'),
            'title' => $stock['page_name'],
            'breadcrumbs' => array(
                array(
                    'url' => wa('shop')->getRouteUrl('shop/frontend/stockList'),
                    'name' => $route_settings['page_name'],
                )
            )
        ));
        $this->setThemeTemplate($route_settings['stock_page_template']);
    }

}
