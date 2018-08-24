<?php

class shopStockPluginFrontendStockListAction extends shopFrontendAction {

    public function execute() {
        if (!($route_settings = shopStockPlugin::isEnabled($route_hash)) || empty($route_settings['stock_page'])) {
            throw new waException(_ws("Page not found"), 404);
        }

        $this->getResponse()->setTitle($route_settings['page_title']);
        $this->getResponse()->setMeta('keywords', $route_settings['meta_keywords']);
        $this->getResponse()->setMeta('description', $route_settings['meta_description']);

        $stock_model = new shopStockPluginModel();
        $stocks = $stock_model->getActiveStocks($route_settings['sort']);
        $this->view->assign('stocks', $stocks);
        
        $frontend_stock_list_template = shopStockHelper::getRouteTemplates($route_hash, 'FrontendStockList', false);
        $html = $this->view->fetch($frontend_stock_list_template['template_path']);

        $this->view->assign('page', array(
            'id' => 'stock',
            'title' => $route_settings['page_title'],
            'name' => $route_settings['page_name'],
            'content' => $html,
        ));
        $this->setThemeTemplate($route_settings['page_template']);
    }

}
