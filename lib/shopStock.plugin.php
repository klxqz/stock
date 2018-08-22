<?php

class shopStockPlugin extends shopPlugin {

    public static $plugin_id = array('shop', 'stock');

    //public static function ($stock) {
    //}

    public static function getStockImageUrl($stock) {
        if (!is_array($stock)) {
            $stock_model = new shopStockPluginModel();
            $stock = $stock_model->getById($stock);
        }
        if (!empty($stock['img'])) {
            $image_path = wa()->getDataPath('/plugins/stock/' . $stock['img'], true, 'shop');
            if (file_exists($image_path)) {
                return wa()->getDataUrl('/plugins/stock/' . $stock['img'], true, 'shop');
            }
        }
        return false;
    }

    private function getRestartPeriod($stock) {
        if (is_array($stock['restart_period'])) {
            return $stock['restart_period'];
        } else {
            return json_decode($stock['restart_period'], true);
        }
    }

    public static function getLastTime(&$stock) {
        $last_time = 0;
        $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
        if ($stock['restart']) {
            if (!$stock['period_runing']) {
                $restart_period = self::getRestartPeriod($stock);
                $current_day = date('D');
                if (in_array($current_day, $restart_period) || in_array('Anyday', $restart_period)) {
                    $period_begin = date('Y-m-d ' . $stock['begin_time']);
                    $period_end = date('Y-m-d H:i:s', strtotime($period_begin) + $stock['duration_time'] * 3600);
                    $stock = array_merge($stock, array(
                        'period_begin' => $period_begin,
                        'period_end' => $period_end,
                        'period_runing' => 1,
                            )
                    );
                    $stock_model = new shopStockPluginModel();
                    $stock_model->updateById($stock['id'], $stock);
                }
            } else {
                $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
                if (strtotime($stock['period_end']) - strtotime($now) <= 0) {
                    $stock = array_merge($stock, array(
                        'period_begin' => '0000-00-00 00:00:00',
                        'period_end' => '0000-00-00 00:00:00',
                        'period_runing' => 0,
                    ));
                    $stock_model = new shopStockPluginModel();
                    $stock_model->updateById($stock['id'], $stock);
                }
            }
            $last_time = strtotime($stock['period_end']) - strtotime($now);
        } else {
            $last_time = strtotime($stock['datetime_end']) - strtotime($now);
        }
        return $last_time;
    }

    public static function display($stock) {
        $app_settings_model = new waAppSettingsModel();
        if (!$app_settings_model->get(self::$plugin_id, 'status')) {
            return false;
        }
        if (!is_array($stock)) {
            $stock_model = new shopStockPluginModel();
            $stock = $stock_model->getById($stock);
        }
        if ($stock) {
            $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
            $time = strtotime($stock['datetime_end']) - strtotime($now);

            $time = self::getLastTime($stock);


            $view = wa()->getView();
            $view->assign('settings', $app_settings_model->get(self::$plugin_id));
            $view->assign('stock', $stock);
            $view->assign('time', $time);
            $template_path = wa()->getDataPath('plugins/stock/templates/Stock.html', false, 'shop', true);
            if (!file_exists($template_path)) {
                $template_path = wa()->getAppPath('plugins/stock/templates/Stock.html', 'shop');
            }
            $html = $view->fetch($template_path);
            return $html;
        }
    }

    public static function getStockById($stock_id) {
        $stock_model = new shopStockPluginModel();
        return $stock_model->getById($stock_id);
    }

    public static function getStockByProduct($product) {
        if (!empty($product['id'])) {
            $product_id = $product['id'];
        } else {
            $product_id = $product;
        }
        $stock_model = new shopStockPluginModel();
        return $stock_model->getStockByProductID($product_id);
    }

    public static function getStockByCategory($category) {
        if (!empty($category['id'])) {
            $category_id = $category['id'];
        } else {
            $category_id = $category;
        }
        $stock_model = new shopStockPluginModel();
        return $stock_model->getStockByCategoryID($category_id);
    }

    public function backendProducts($params) {
        if ($this->getSettings('status')) {
            $plugin_url = $this->getPluginStaticUrl();
            $stock_model = new shopStockPluginModel();
            $stocks = $stock_model->getAll();
            $count = count($stocks);
            $sidebar_top_li_html = <<<HTML
<li id="s-stocks">
<a href = "#/stockList/">
    <span class="count">{$count}</span>
    <i class="icon16" style="background-image: url({$plugin_url}img/stock.png);"></i>
    Акции
</a>
<script type="text/javascript" src = "{$plugin_url}js/stock.js"></script>
</li>

<link href="{$plugin_url}css/jquery-ui/jquery-ui-timepicker-addon.min.css" rel="stylesheet" type="text/css" />  
<script type="text/javascript" src="{$plugin_url}js/jquery-ui/jquery-ui-timepicker-addon.min.js"></script>
HTML;
            $lang = substr(wa()->getLocale(), 0, 2);
            if ($lang == 'ru') {
                $sidebar_top_li_html .= '<script type="text/javascript" src="' . $plugin_url . 'js/jquery-ui/i18n/jquery-ui-timepicker-ru.js"></script>';
            }

            $toolbar_organize_li_html = '<div style="display: none;" id="stock-dialog"></div><li data-action="stock"><a class="add-stock-products" href="#"><i class="icon16 add"></i>Добавить в акцию</a></li>';

            return array(
                'sidebar_top_li' => $sidebar_top_li_html,
                'toolbar_organize_li' => $toolbar_organize_li_html
            );
        }
    }

    public function frontendHomepage() {
        if ($this->getSettings('status')) {
            $stock_model = new shopStockPluginModel();
            $stocks = $stock_model->getActiveStocks();
            $html = '';
            foreach ($stocks as $stock) {
                if ($stock['homepage']) {
                    $html .= self::display($stock);
                }
            }
            return $html;
        }
    }

    public function frontendCart() {
        if ($this->getSettings('status')) {
            $cart = new shopCart();
            $items = $cart->items();
            $stock_model = new shopStockPluginModel();

            $product_ids = array();
            foreach ($items as $item) {
                if ($stock = $stock_model->getStockByProductID($item['product_id'])) {
                    if ($stock['type'] == 'gift' && $stock['gift_sku_id']) {
                        $sku_model = new shopProductSkusModel();
                        if ($sku = $sku_model->getById($stock['gift_sku_id'])) {
                            $product_ids[] = $sku['product_id'];
                        }
                    }
                }
            }

            $collection = new shopProductsCollection('id/' . implode(',', $product_ids));
            $gift_products = $collection->getProducts('*', 0, 99999, true);
            if ($gift_products) {
                $view = wa()->getView();
                $view->assign('include_template', $this->getSettings('cart_products_template'));
                $view->assign('gift_products', $gift_products);
                $template_path = wa()->getDataPath('plugins/stock/templates/FrontendCart.html', false, 'shop', true);
                if (!file_exists($template_path)) {
                    $template_path = wa()->getAppPath('plugins/stock/templates/FrontendCart.html', 'shop');
                }
                $html = $view->fetch($template_path);
                return $html;
            }
        }
    }

    public function frontendProduct($product) {
        if ($this->getSettings('status') && $this->getSettings('frontend_product_output')) {
            $stock_model = new shopStockPluginModel();
            if ($stock = $stock_model->getStockByProductID($product->id)) {
                $html = self::display($stock);
                return array($this->getSettings('frontend_product_output') => $html);
            }
        }
    }

    public function frontendCategory($category) {
        if ($this->getSettings('status') && $this->getSettings('frontend_category_output')) {
            $stock_model = new shopStockPluginModel();
            if ($stock = $stock_model->getStockByCategoryID($category['id'])) {
                $html = self::display($stock);
                return $html;
            }
        }
    }

    public function frontendProducts(&$params) {
        if ($this->getSettings('status') && !wa()->getStorage()->get('shop/stockplugin/frontendProductsOff')) {
            if (!empty($params['products'])) {
                $params['products'] = $this->prepareProducts($params['products']);
            }
            if (!empty($params['skus'])) {
                $params['skus'] = $this->prepareSkus($params['skus']);
            }
        }
    }

    private function prepareProducts($products = array()) {
        $stock_model = new shopStockPluginModel();
        $product_ids = array();
        foreach ($products as $product) {
            $product_ids[] = $product['id'];
        }
        $products_stocks = $stock_model->getStockByProducts($product_ids);

        foreach ($products as &$product) {
            if (!empty($products_stocks[$product['id']])) {
                $stock = $products_stocks[$product['id']];
                $this->updatePrices($stock, $product);
                $this->setBadge($stock, $product);
            }
        }
        unset($product);

        return $products;
    }

    private function prepareSkus($skus = array()) {
        $stock_model = new shopStockPluginModel();
        $product_ids = array();
        foreach ($skus as $sku) {
            $product_ids[] = $sku['product_id'];
        }
        $products_stocks = $stock_model->getStockByProducts($product_ids);

        foreach ($skus as &$sku) {
            if (!empty($products_stocks[$sku['product_id']])) {
                $stock = $products_stocks[$sku['product_id']];
                $this->updatePrices($stock, $sku);
            }
        }
        unset($sku);

        return $skus;
    }

    private function setBadge($stock, &$product) {
        $badge = null;
        if (!empty($stock['badge']) && $stock['badge'] != 'code') {
            $badge = $stock['badge'];
        } elseif ($stock['badge'] == 'code') {
            $badge = $stock['badge_code'];
        }

        if ($stock['multiple_badges'] && $badge) {
            if ($product['badge']) {
                $product['badge'] = shopHelper::getBadgeHtml($product['badge']);
            }
            $product['badge'] .= shopHelper::getBadgeHtml($badge);
        } elseif ($badge) {
            $product['badge'] = $badge;
        }
    }

    private function checkPromoCode($stock) {
        if ($stock['promocode_only'] && $stock['promocode']) {
            $promocode = wa()->getStorage()->read('stock_plugin/promocode');
            if ($promocode != $stock['promocode']) {
                return false;
            }
        }
        return true;
    }

    private function updatePrices($stock, &$item) {
        if ($stock['type'] == 'discount' && $stock['discount_algorithm'] == 'replace' && $stock['discount_value'] > 0 && isset($item['price']) && $this->checkPromoCode($stock)) {
            $old_price = $item['price'];
            if ($stock['discount_type'] == 'percent') {
                $item['price'] = $item['price'] * (100 - $stock['discount_value']) / 100.0;
                if ($stock['rounding']) {
                    $item['price'] = round($item['price']);
                }
            } elseif ($stock['discount_type'] == 'absolute') {
                $discount_value = $stock['discount_value'];
                if (!empty($item['product_id'])) {
                    $def_currency = wa('shop')->getConfig()->getCurrency(true);
                    $product_model = new shopProductModel();
                    $product = $product_model->getById($item['product_id']);
                    $discount_value = shop_currency($discount_value, $def_currency, $product['currency'], false);
                }
                $item['price'] = $item['price'] - $discount_value;
            } elseif ($stock['discount_type'] == 'price') {
                $new_price = $stock['discount_value'];
                if (!empty($item['product_id'])) {
                    $def_currency = wa('shop')->getConfig()->getCurrency(true);
                    $product_model = new shopProductModel();
                    $product = $product_model->getById($item['product_id']);
                    $new_price = shop_currency($new_price, $def_currency, $product['currency'], false);
                }
                $item['price'] = $new_price;
            }

            if (isset($item['compare_price']) && $item['compare_price'] == 0) {
                $item['compare_price'] = $old_price;
            }
        }
    }

    public function orderCalculateDiscount($params) {
        if ($this->getSettings('status')) {
            $post = waRequest::post();
            if (isset($post['coupon_code'])) {
                wa()->getStorage()->write('stock_plugin/promocode', $post['coupon_code']);
            }

            $stock_model = new shopStockPluginModel();
            $discount = array();
            foreach ($params['order']['items'] as $item_id => $item) {
                if ($item['type'] == 'product') {
                    $stock = $stock_model->getStockByProductID($item['product_id']);
                    if (!empty($stock) && $stock['type'] == 'discount' && $stock['discount_algorithm'] == 'standart' && $stock['discount_value'] > 0 && $this->checkPromoCode($stock)) {
                        $stock_discount_value = 0;
                        if ($stock['discount_type'] == 'percent') {
                            $stock_discount_value = shop_currency($item['price'] * $stock['discount_value'] / 100.0, $item['currency'], $params['order']['currency'], false);
                        } elseif ($stock['discount_type'] == 'absolute') {
                            $def_currency = wa('shop')->getConfig()->getCurrency(true);
                            $stock_discount_value = shop_currency($stock['discount_value'], $def_currency, $params['order']['currency'], false);
                        } elseif ($stock['discount_type'] == 'price') {
                            $def_currency = wa('shop')->getConfig()->getCurrency(true);
                            $new_price = shop_currency($stock['discount_value'], $def_currency, $item['currency'], false);
                            $stock_discount_value = shop_currency($item['price'] - $new_price, $item['currency'], $params['order']['currency'], false);
                        }
                        $discount['items'][$item_id] = array(
                            'discount' => $stock_discount_value * $item['quantity'],
                            'description' => "Скидка по акции «{$stock['name']}»",
                        );
                    }
                }
            }
            return $discount;
        }
    }

    public function orderActionCreate($params) {
        if ($this->getSettings('status')) {
            $add_gift_flag = false;
            $order_id = $params['order_id'];
            $contact_id = $params['contact_id'];
            $order_model = new shopOrderModel();
            $order_items_model = new shopOrderItemsModel();
            $log_model = new shopOrderLogModel();

            $order = $order_model->getById($order_id);
            $order['contact'] = new waContact($contact_id);
            $order['items'] = $order_items_model->getItems($order_id);


            $def_currency = wa('shop')->getConfig()->getCurrency(true);
            $product_percent_bonus = 0;
            $stock_model = new shopStockPluginModel();
            foreach ($order['items'] as $item) {
                if (empty($item['product_id'])) {
                    continue;
                }
                if ($stock = $stock_model->getStockByProductID($item['product_id'])) {
                    if ($stock['type'] == 'bonus' && $stock['product_percent_bonus']) {
                        $product_bonus = $item['price'] * $item['quantity'] * $stock['product_percent_bonus'] / 100.0;
                        $product_percent_bonus += shop_currency($product_bonus, $def_currency, $order['currency'], false);
                    } elseif ($stock['type'] == 'gift' && $stock['gift_sku_id']) {
                        $sku_model = new shopProductSkusModel();
                        if ($sku = $sku_model->getById($stock['gift_sku_id'])) {
                            $product_model = new shopProductModel();
                            $product = $product_model->getById($sku['product_id']);
                            $add_item = array(
                                'order_id' => $order_id,
                                'name' => $product['name'] . ' (Подарок)',
                                'product_id' => $product['id'],
                                'sku_id' => $sku['id'],
                                'sku_code' => $sku['sku'],
                                'type' => 'product',
                                'service_id' => null,
                                'service_variant_id' => null,
                                'price' => 0,
                                'quantity' => $item['quantity'],
                                'stock_id' => null,
                            );
                            $order['items'][] = $add_item;

                            $name = $product['name'];

                            $log_data = array(
                                'action_id' => 'comment',
                                'order_id' => $order_id,
                                'before_state_id' => $order['state_id'],
                                'after_state_id' => $order['state_id'],
                                'text' => 'Плагин «<a target="_blank" href="?action=plugins#/stock/">Акции</a>»: '
                                . 'К заказу добавлен подарок <a target="_blank" href="?action=products#/product/' . $product['id'] . '/">' . $name . '</a> '
                                . 'согласно условию акции «<a target="_blank" href="?action=products#/stock/' . $stock['id'] . '/">' . $stock['name'] . '</a>»',
                            );
                            $log_model->add($log_data);
                            $add_gift_flag = true;
                        }
                    }
                }
            }

            $stock_absolute_bonus = 0;
            foreach ($order['items'] as $item) {
                $stock = $stock_model->getStockByProductID($item['product_id']);
                if ($stock['type'] == 'bonus' && $stock['absolute_bonus']) {
                    $stock_absolute_bonus = $stock['absolute_bonus'];
                    break;
                }
            }

            if ($stock_absolute_bonus || $product_percent_bonus) {
                $total_bonus = $product_percent_bonus + $stock_absolute_bonus;
                $comment = sprintf("Начисление бонусов за заказ %s", shopHelper::encodeOrderId($order_id));
                $atm = new shopAffiliateTransactionModel();
                $atm->applyBonus($contact_id, $total_bonus, $order_id, $comment);
            }

            if ($add_gift_flag) {
                $order['discount'] = shopDiscounts::calculate($order);

                $workflow = new shopWorkflow();
                $workflow->getActionById('edit')->run($order);
            }
        }
    }

    public function routing($route = array()) {
        if ($this->getSettings('status')) {
            $routing = array();
            if ($this->getSettings('stock_page')) {
                $routing[$this->getSettings('page_url')] = 'frontend/stockList';
                $routing[$this->getSettings('page_url') . '<stock>/'] = 'frontend/stock';
            }
            return $routing;
        }
    }

    public function productsCollection($params) {
        if ($this->getSettings('status')) {
            $collection = $params['collection'];
            $hash = $collection->getHash();
            if ($hash[0] !== 'stock') {
                return false;
            }
            $cache_id = md5('shopStockPlugin::productsCollection' . $hash[1]);
            $cache_time = wa()->getConfig()->isDebug() ? 0 : 7200;
            $cache = new waSerializeCache($cache_id, $cache_time, 'shop/plugins/stock');
            if ($cache && $cache->isCached()) {
                $where = $cache->get();
            } else {
                $stock_products_model = new shopStockProductsPluginModel();
                $stock_products = $stock_products_model->getByField('stock_id', $hash[1], true);
                $product_ids = array();
                $product_types = array();
                foreach ($stock_products as $stock_product) {
                    switch ($stock_product['type']) {
                        case 'product':
                            $product_ids[] = $stock_products_model->escape($stock_product['value']);
                            break;
                        case 'category':
                            $category_collection = new shopProductsCollection('category/' . $stock_product['value']);
                            $products = $category_collection->getProducts('*', 0, 99999, true);
                            if ($products) {
                                $product_ids = array_merge($product_ids, array_keys($products));
                            }
                            break;
                        case 'type':
                            $product_types[] = $stock_products_model->escape($stock_product['value']);
                            break;
                        case 'set':
                            $set_collection = new shopProductsCollection('set/' . $stock_product['value']);
                            $products = $set_collection->getProducts('*', 0, 99999, true);
                            if ($products) {
                                $product_ids = array_merge($product_ids, array_keys($products));
                            }
                            break;
                        case 'feature':
                            $val = explode(':', $stock_product['value']);
                            $feature_model = new shopFeatureModel();
                            $feature = $feature_model->getById($val[0]);
                            $feature_data = array($feature['code'] => $val[1]);
                            $feature_collection = new shopProductsCollection();
                            $feature_collection->filters($feature_data);
                            $products = $feature_collection->getProducts('*', 0, 99999, true);
                            if ($products) {
                                $product_ids = array_merge($product_ids, array_keys($products));
                            }
                            break;
                    }
                }
                $where = array();
                if ($product_ids) {
                    $where[] = "`id` IN (" . implode(',', array_unique($product_ids)) . ")";
                }
                if ($product_types) {
                    $where[] = "`type_id` IN (" . implode(',', array_unique($product_types)) . ")";
                }
                if ($cache) {
                    $cache->set($where);
                }
            }
            if ($where) {
                $collection->addWhere(implode(" OR ", $where));
            } else {
                $collection->addWhere("`id` IN (NULL)");
            }
            return true;
        }
    }

    public function sitemap($route) {
        if ($this->getSettings('status') && $this->getSettings('stock_page')) {
            $urls = array();

            $urls[] = array(
                'loc' => wa()->getRouteUrl('shop/frontend/stockList', true),
                'changefreq' => waSitemapConfig::CHANGE_MONTHLY,
                'priority' => 0.2
            );
            $stock_model = new shopStockPluginModel();
            $stocks = $stock_model->getActiveStocks();
            foreach ($stocks as $stock) {
                $urls[] = array(
                    'loc' => wa()->getRouteUrl('shop/frontend/stock', array('stock' => $stock['page_url']), true),
                    'changefreq' => waSitemapConfig::CHANGE_MONTHLY,
                    'priority' => 0.2
                );
            }
            return $urls;
        }
    }

    public function stockInfo($product_id) {
        return false;
    }

    public static function shortList() {
        return false;
    }

}
