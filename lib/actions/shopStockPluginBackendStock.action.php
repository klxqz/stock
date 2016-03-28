<?php

class shopStockPluginBackendStockAction extends waViewAction {

    private $models = array();

    public function execute() {
        $id = waRequest::get('id', 0, waRequest::TYPE_INT);
        if ($id) {
            $stock_model = new shopStockPluginModel();
            $stock = $stock_model->getById($id);
            if (!empty($stock['gift_sku_id'])) {
                $sku_model = new shopProductSkusModel();
                $gift_sku = $sku_model->getById($stock['gift_sku_id']);
                if (!empty($gift_sku)) {
                    $product_model = new shopProductModel();
                    $gift_product = $product_model->getById($gift_sku['product_id']);
                    $gift_product['sku'] = $gift_sku;
                    $gift_sku = $this->view->assign('gift_product', $gift_product);
                }
            }

            $this->view->assign('stock', $stock);
            $stock_products_model = new shopStockProductsPluginModel();
            $stock_products = $stock_products_model->getByField('stock_id', $id, true);
            $stock_products = $this->prepareStockProducts($stock_products);
            $this->view->assign('stock_products', $stock_products);
        }

        $set_model = new shopSetModel();
        $type_model = new shopTypeModel();
        $lang = substr(wa()->getLocale(), 0, 2);
        $def_currency = wa('shop')->getConfig()->getCurrency(true);

        $app_settings_model = new waAppSettingsModel();
        $page_url = $app_settings_model->get(shopStockPlugin::$plugin_id, 'page_url');

        $this->view->assign('frontend_url', wa()->getRouteUrl('shop/frontend'));
        $this->view->assign('page_url', $page_url);
        $this->view->assign('lang', $lang);
        $this->view->assign('sets', $set_model->getAll());
        $this->view->assign('types', $type_model->getTypes());
        $this->view->assign('categories', $this->getCategories());
        $this->view->assign('def_currency', $def_currency);
    }

    private function prepareStockProducts($stock_products) {
        foreach ($stock_products as &$stock_product) {
            $model = $this->getModel($stock_product['type']);
            $item = $model->getById($stock_product['value']);
            if ($item) {
                $stock_product['name'] = $item['name'];
            }
        }
        unset($stock_product);
        return $stock_products;
    }

    private function getModel($type) {
        $model_name = '';
        switch ($type) {
            case 'product':
                $model_name = 'shopProductModel';
                break;
            case 'set':
                $model_name = 'shopSetModel';
                break;
            case 'type':
                $model_name = 'shopTypeModel';
                break;
            case 'category':
                $model_name = 'shopCategoryModel';
                break;
        }

        if ($model_name && class_exists($model_name)) {
            if (empty($this->models[$model_name])) {
                $this->models[$model_name] = new $model_name();
            }
            return $this->models[$model_name];
        } else {
            throw new Exception('Не определена модель');
        }
    }

    private function getCategories() {

        $category_model = new shopCategoryModel();
        $route = null;
        $cats = $category_model->getTree(null, null, false, $route);


        $stack = array();
        $result = array();
        foreach ($cats as $c) {
            $c['childs'] = array();

            // Number of stack items
            $l = count($stack);

            // Check if we're dealing with different levels
            while ($l > 0 && $stack[$l - 1]['depth'] >= $c['depth']) {
                array_pop($stack);
                $l--;
            }

            // Stack is empty (we are inspecting the root)
            if ($l == 0) {
                // Assigning the root node
                $i = count($result);
                $result[$i] = $c;
                $stack[] = &$result[$i];
            } else {
                // Add node to parent
                $i = count($stack[$l - 1]['childs']);
                $stack[$l - 1]['childs'][$i] = $c;
                $stack[] = &$stack[$l - 1]['childs'][$i];
            }
        }
        return $result;
    }

}
