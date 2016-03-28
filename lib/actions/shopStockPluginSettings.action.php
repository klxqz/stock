<?php

class shopStockPluginSettingsAction extends waViewAction {

    private $templates = array(
        'Stock' => array(
            'name' => 'Шаблон акции',
            'tpl_path' => 'plugins/stock/templates/',
            'tpl_name' => 'Stock',
            'tpl_ext' => 'html',
            'public' => false
        ),
        'FrontendCart' => array(
            'name' => 'Шаблон вывода подарков в корзине',
            'tpl_path' => 'plugins/stock/templates/',
            'tpl_name' => 'FrontendCart',
            'tpl_ext' => 'html',
            'public' => false
        ),
        'FrontendStockList' => array(
            'name' => 'Шаблон страницы «Список акций»',
            'tpl_path' => 'plugins/stock/templates/actions/frontend/',
            'tpl_name' => 'FrontendStockList',
            'tpl_ext' => 'html',
            'public' => false
        ),
        'FrontendStock' => array(
            'name' => 'Шаблон страницы акции',
            'tpl_path' => 'plugins/stock/templates/actions/frontend/',
            'tpl_name' => 'FrontendStock',
            'tpl_ext' => 'html',
            'public' => false
        ),
    );

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        $settings = $app_settings_model->get(shopStockPlugin::$plugin_id);

        $templates = array();
        foreach ($this->templates as $template_id => $template) {
            $tpl_full_path = $template['tpl_path'] . $template['tpl_name'] . '.' . $template['tpl_ext'];
            $template_path = wa()->getDataPath($tpl_full_path, $template['public'], 'shop', true);
            if (file_exists($template_path)) {
                $template['template'] = file_get_contents($template_path);
                $template['change_tpl'] = 1;
            } else {
                $template_path = wa()->getAppPath($tpl_full_path, 'shop');
                $template['template'] = file_get_contents($template_path);
                $template['change_tpl'] = 0;
            }
            $templates[$template_id] = $template;
        }

        $this->view->assign('templates', $templates);
        $this->view->assign('settings', $settings);
    }

}
