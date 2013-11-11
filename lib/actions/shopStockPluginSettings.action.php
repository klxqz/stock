<?php

class shopStockPluginSettingsAction extends waViewAction {

    protected $tmp_path = 'plugins/stock/templates/FrontendNav.html';
    
    public function execute() {
        $plugin = wa()->getPlugin('stock');
        $settings = $plugin->getSettings();
        $change_tpl = false;
        $template_path = wa()->getDataPath($this->tmp_path, false, 'shop', true);
        if (file_exists($template_path)) {
            $change_tpl = true;
        } else {
            $template_path = wa()->getAppPath($this->tmp_path, 'shop');
        }
        $template = file_get_contents($template_path);
        $this->view->assign('settings', $settings);
        $this->view->assign('template', $template);
        $this->view->assign('change_tpl', $change_tpl);
    }

}
