<?php

class shopStockPluginSettingsRouteAction extends waViewAction {

    public function execute() {
        $route_hash = waRequest::get('route_hash');
        $view = wa()->getView();
        $view->assign(array(
            'route_hash' => $route_hash,
            'route_settings' => shopStockHelper::getRouteSettings($route_hash),
            'templates' => shopStockHelper::getRouteTemplates($route_hash),
        ));
    }

}
