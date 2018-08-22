<?php

class shopStockPluginFrontendStockAction extends shopFrontendAction {

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        if (!$app_settings_model->get(shopStockPlugin::$plugin_id, 'status')) {
            throw new waException(_ws("Page not found"), 404);
        }
        $page_url = waRequest::param('stock');
        $stock_model = new shopStockPluginModel();
        $stock = $stock_model->getByField('page_url', $page_url);
        if (!$stock) {
            throw new waException(_ws("Page not found"), 404);
        }

        $this->getResponse()->setTitle($stock['page_title']);
        $this->getResponse()->setMeta('keywords', $stock['meta_keywords']);
        $this->getResponse()->setMeta('description', $stock['meta_description']);

        $this->view->assign('stock', $stock);
        $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
        //$time = strtotime($stock['datetime_end']) - strtotime($now);
        $time = shopStockPlugin::getLastTime($stock);
        if ($time <= 0) {
            throw new waException(_ws("Page not found"), 404);
        }

        $this->view->assign('time', $time);
        $settings = $app_settings_model->get(shopStockPlugin::$plugin_id);
        $this->view->assign('settings', $settings);

        $collection = new shopProductsCollection('stock/' . $stock['id']);
        $this->setCollection($collection);


        $template_path = wa()->getDataPath('plugins/stock/templates/actions/frontend/FrontendStock.html', false, 'shop', true);
        if (!file_exists($template_path)) {
            $template_path = wa()->getAppPath('plugins/stock/templates/actions/frontend/FrontendStock.html', 'shop');
        }
        $html = $this->view->fetch($template_path);

        $this->view->assign('frontend_search', array('stock' => $html) + wa()->event('frontend_search'));
        $this->view->assign('title', $stock['page_name']);
        $this->view->assign('breadcrumbs', array(
            array(
                'url' => wa()->getRouteUrl('shop/frontend/stockList'),
                'name' => $settings['page_name'],
            )
        ));

        $this->setThemeTemplate($settings['stock_page_template']);
    }

}
