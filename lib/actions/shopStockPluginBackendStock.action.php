<?php

class shopStockPluginBackendStockAction extends waViewAction {

    private $models = array();

    public function execute() {
        $id = waRequest::get('id', 0, waRequest::TYPE_INT);
        if ($id) {
            $stock_model = new shopStockPluginModel();
            $stock = $stock_model->getById($id);
            if (!empty($stock['restart_period'])) {
                $stock['restart_period'] = json_decode($stock['restart_period'], true);
            } else {
                $stock['restart_period'] = array();
            }
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
        $this->view->assign('features_filter', $this->getFeaturesFilter());
        $this->view->assign('default_promocode', $this->generateCode());
    }

    protected function generateCode() {
        $alphabet = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890";
        $result = '';
        while (strlen($result) < 8) {
            $result .= $alphabet{mt_rand(0, strlen($alphabet) - 1)};
        }
        return $result;
    }

    protected function getFeaturesFilter() {
        $feature_model = new shopFeatureModel();
        $features = $feature_model->getFeatures(true, null, 'id');

        $collection = new shopProductsCollection();

        $filter_ids = array();
        foreach ($features as $feature) {
            $filter_ids[] = $feature['id'];
        }

        $feature_model = new shopFeatureModel();
        $features = $feature_model->getById(array_filter($filter_ids, 'is_numeric'));
        if ($features) {
            $features = $feature_model->getValues($features);
        }
        $category_value_ids = $collection->getFeatureValueIds();

        $filters = array();
        foreach ($filter_ids as $fid) {
            if ($fid == 'price') {
                $range = $collection->getPriceRange();
                if ($range['min'] != $range['max']) {
                    $filters['price'] = array(
                        'min' => shop_currency($range['min'], null, null, false),
                        'max' => shop_currency($range['max'], null, null, false),
                    );
                }
            } elseif (isset($features[$fid]) && isset($category_value_ids[$fid])) {
                $filters[$fid] = $features[$fid];
                $min = $max = $unit = null;
                foreach ($filters[$fid]['values'] as $v_id => $v) {
                    if (!in_array($v_id, $category_value_ids[$fid])) {
                        unset($filters[$fid]['values'][$v_id]);
                    } else {
                        if ($v instanceof shopRangeValue) {
                            $begin = $this->getFeatureValue($v->begin);
                            if ($min === null || $begin < $min) {
                                $min = $begin;
                            }
                            $end = $this->getFeatureValue($v->end);
                            if ($max === null || $end > $max) {
                                $max = $end;
                                if ($v->end instanceof shopDimensionValue) {
                                    $unit = $v->end->unit;
                                }
                            }
                        } else {
                            $tmp_v = $this->getFeatureValue($v);
                            if ($min === null || $tmp_v < $min) {
                                $min = $tmp_v;
                            }
                            if ($max === null || $tmp_v > $max) {
                                $max = $tmp_v;
                                if ($v instanceof shopDimensionValue) {
                                    $unit = $v->unit;
                                }
                            }
                        }
                    }
                }
                if (!$filters[$fid]['selectable'] && ($filters[$fid]['type'] == 'double' ||
                        substr($filters[$fid]['type'], 0, 6) == 'range.' ||
                        substr($filters[$fid]['type'], 0, 10) == 'dimension.')) {
                    if ($min == $max) {
                        unset($filters[$fid]);
                    } else {
                        $type = preg_replace('/^[^\.]*\./', '', $filters[$fid]['type']);
                        if ($type != 'double') {
                            $filters[$fid]['base_unit'] = shopDimension::getBaseUnit($type);
                            $filters[$fid]['unit'] = shopDimension::getUnit($type, $unit);
                            if ($filters[$fid]['base_unit']['value'] != $filters[$fid]['unit']['value']) {
                                $dimension = shopDimension::getInstance();
                                $min = $dimension->convert($min, $type, $filters[$fid]['unit']['value']);
                                $max = $dimension->convert($max, $type, $filters[$fid]['unit']['value']);
                            }
                        }
                        $filters[$fid]['min'] = $min;
                        $filters[$fid]['max'] = $max;
                    }
                }
            }
        }

        return $filters;
    }

    protected function getFeatureValue($v) {
        if ($v instanceof shopDimensionValue) {
            return $v->value_base_unit;
        }
        if (is_object($v)) {
            return $v->value;
        }
        return $v;
    }

    private function prepareStockProducts($stock_products) {
        foreach ($stock_products as &$stock_product) {
            if ($stock_product['type'] != 'feature') {
                $model = $this->getModel($stock_product['type']);
                $item = $model->getById($stock_product['value']);
                if ($item) {
                    $stock_product['name'] = $item['name'];
                }
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
            case 'feature':
                $model_name = 'shopFeatureModel';
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
