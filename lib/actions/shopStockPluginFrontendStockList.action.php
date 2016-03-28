<?php

class shopStockPluginFrontendStockListAction extends shopFrontendAction {

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        if (!$app_settings_model->get(shopStockPlugin::$plugin_id, 'status')) {
            throw new waException(_ws("Page not found"), 404);
        }

        $settings = $app_settings_model->get(shopStockPlugin::$plugin_id);

        $this->getResponse()->setTitle($settings['page_title']);
        $this->getResponse()->setMeta('keywords', $settings['meta_keywords']);
        $this->getResponse()->setMeta('description', $settings['meta_description']);

        $stock_model = new shopStockPluginModel();
        $stocks = $stock_model->getActiveStocks();
        $this->view->assign('stocks', $stocks);
        $template_path = wa()->getDataPath('plugins/stock/templates/actions/frontend/FrontendStockList.html', false, 'shop', true);
        if (!file_exists($template_path)) {
            $template_path = wa()->getAppPath('plugins/stock/templates/actions/frontend/FrontendStockList.html', 'shop');
        }
        
        $html = $this->view->fetch($template_path);

        $this->view->assign('page', array(
            'id' => 'stock',
            'title' => $settings['page_title'],
            'name' => $settings['page_name'],
            'content' => $html,
        ));
        $this->setThemeTemplate($settings['page_template']);
    }

}
