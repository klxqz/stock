<?php

class shopStockPlugin extends shopPlugin {

    public function backendProduct($product) {
        $view = wa()->getView();
        $view->assign('product', $product);
        $html = $view->fetch('plugins/stock/templates/BackendProduct.html');
        return array('edit_section_li' => $html);
    }

}
