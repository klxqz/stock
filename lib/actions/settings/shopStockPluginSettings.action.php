<?php

class shopStockPluginSettingsAction extends waViewAction {

    public function execute() {
        $this->view->assign(array(
            'templates' => shopStockPlugin::$templates,
            'plugin' => wa()->getPlugin('stock'),
            'route_hashs' => shopStockHelper::getRouteHashs(),
        ));
    }

}
