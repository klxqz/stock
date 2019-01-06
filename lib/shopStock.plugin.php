<?php

class shopStockPlugin extends shopPlugin {

    public static $templates = array(
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

    /**
     * @param array $settings
     */
    public function saveSettings($settings = array()) {
        $route_hash = waRequest::post('route_hash');
        $route_settings = waRequest::post('route_settings');

        if ($routes = $this->getSettings('routes')) {
            $settings['routes'] = $routes;
        } else {
            $settings['routes'] = array();
        }
        $settings['routes'][$route_hash] = $route_settings;
        $settings['route_hash'] = $route_hash;
        parent::saveSettings($settings);


        $templates = waRequest::post('templates');
        foreach ($templates as $template_id => $template) {
            $s_template = self::$templates[$template_id];
            if (!empty($template['reset_tpl']) || waRequest::post('reset_tpl_all')) {
                $tpl_full_path = $s_template['tpl_path'] . $route_hash . '.' . $s_template['tpl_name'] . '.' . $s_template['tpl_ext'];
                $template_path = wa()->getDataPath($tpl_full_path, $s_template['public'], 'shop', true);
                @unlink($template_path);
            } else {
                $tpl_full_path = $s_template['tpl_path'] . $route_hash . '.' . $s_template['tpl_name'] . '.' . $s_template['tpl_ext'];
                $template_path = wa()->getDataPath($tpl_full_path, $s_template['public'], 'shop', true);
                if (!file_exists($template_path)) {
                    $tpl_full_path = $s_template['tpl_path'] . $s_template['tpl_name'] . '.' . $s_template['tpl_ext'];
                    $template_path = wa()->getAppPath($tpl_full_path, 'shop');
                }
                $content = file_get_contents($template_path);
                if (!empty($template['template']) && strcmp(str_replace("\r", "", $template['template']), str_replace("\r", "", $content)) != 0) {
                    $tpl_full_path = $s_template['tpl_path'] . $route_hash . '.' . $s_template['tpl_name'] . '.' . $s_template['tpl_ext'];
                    $template_path = wa()->getDataPath($tpl_full_path, $s_template['public'], 'shop', true);
                    $f = fopen($template_path, 'w');
                    if (!$f) {
                        throw new waException('Не удаётся сохранить шаблон. Проверьте права на запись ' . $template_path);
                    }
                    fwrite($f, $template['template']);
                    fclose($f);
                }
            }
        }
    }

    public static function isEnabled(&$route_hash = null) {
        if (!wa('shop')->getPlugin('stock')->getSettings('status')) {
            return false;
        }
        if (shopStockHelper::getRouteSettings(null, 'status')) {
            $route_hash = null;
            return shopStockHelper::getRouteSettings();
        } elseif (shopStockHelper::getRouteSettings(0, 'status')) {
            $route_hash = 0;
            return shopStockHelper::getRouteSettings(0);
        } else {
            return false;
        }
    }

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

    public static function display($stock) {
        if (!($route_settings = self::isEnabled($route_hash))) {
            return;
        }
        if (!is_array($stock)) {
            $stock_model = new shopStockPluginModel();
            $stock = $stock_model->getById($stock);
            $stock['params'] = json_decode($stock['params'], true);
        }
        if ($stock) {
            $time = shopStockHelper::getLastTime($stock);
            $view = wa()->getView();
            $view->assign(array(
                'settings' => $route_settings,
                'stock' => $stock,
                'time' => $time,
            ));
            $stock_template = shopStockHelper::getRouteTemplates($route_hash, 'Stock', false);
            $html = $view->fetch($stock_template['template_path']);
            return $html;
        }
    }

    public static function getStockById($stock_id) {
        $stock_model = new shopStockPluginModel();
        return $stock_model->getById($stock_id);
    }

    public static function getStockByProduct($product) {
        if (is_array($product) && !empty($product['id'])) {
            $product_id = $product['id'];
        } elseif ($product instanceof shopProduct) {
            $product_id = $product->id;
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
        if (!$this->getSettings('status')) {
            return;
        }
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

    public function frontendHomepage() {
        if (!($route_settings = self::isEnabled())) {
            return;
        }
        $stock_model = new shopStockPluginModel();
        $stocks = $stock_model->getActiveStocks($route_settings['sort']);
        $html = '';
        foreach ($stocks as $stock) {
            if ($stock['homepage']) {
                $html .= self::display($stock);
            }
        }
        return $html;
    }

    public function frontendCart() {
        if (!($route_settings = self::isEnabled($route_hash))) {
            return;
        }
        $cart = new shopCart();
        $items = $cart->items();

        $product_ids = array();
        foreach ($items as $item) {
            if (!empty($item['product_id'])) {
                $product_ids[] = $item['product_id'];
            }
        }
        $stock_model = new shopStockPluginModel();
        $stocks = $stock_model->getStockByProducts($product_ids);

        $sku_model = new shopProductSkusModel();
        $gift_product_ids = array();
        foreach ($stocks as $stock) {
            if ($stock['type'] == 'gift' && $stock['gift_sku_id']) {
                if ($sku = $sku_model->getById($stock['gift_sku_id'])) {
                    $gift_product_ids[] = $sku['product_id'];
                }
            }
        }

        $collection = new shopProductsCollection('id/' . implode(',', $gift_product_ids));
        $gift_products = $collection->getProducts('*', 0, 99999, true);
        if ($gift_products) {
            $view = wa()->getView();
            $view->assign('include_template', $route_settings['cart_products_template']);
            $view->assign('gift_products', $gift_products);
            $frontend_cart_template = shopStockHelper::getRouteTemplates($route_hash, 'FrontendCart', false);
            $html = $view->fetch($frontend_cart_template['template_path']);
            return $html;
        }
    }

    public function frontendProduct($product) {
        if (!($route_settings = self::isEnabled()) || empty($route_settings['frontend_product_output'])) {
            return;
        }

        if ($stock = self::getStockByProduct($product->id)) {
            $html = self::display($stock);
            return array($route_settings['frontend_product_output'] => $html);
        }
    }

    public function frontendCategory($category) {
        if (!($route_settings = self::isEnabled()) || empty($route_settings['frontend_category_output'])) {
            return;
        }

        if ($stock = self::getStockByCategory($category['id'])) {
            $html = self::display($stock);
            return $html;
        }
    }

    public function frontendProducts(&$params) {
        if (self::isEnabled()) {
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
        if (!($route_settings = self::isEnabled())) {
            return;
        }

        wa()->getStorage()->write('stock_plugin/promocode', waRequest::post('coupon_code'));
        $product_ids = array();
        foreach ($params['order']['items'] as $item_id => $item) {
            if (!empty($item['product_id'])) {
                $product_ids[] = $item['product_id'];
            }
        }

        $stock_model = new shopStockPluginModel();
        $stocks = $stock_model->getStockByProducts($product_ids);
        if (!$stocks) {
            return;
        }

        $discount = array();
        foreach ($params['order']['items'] as $item_id => $item) {
            if ($item['type'] == 'product' && !empty($stocks[$item['product_id']])) {
                $stock = $stocks[$item['product_id']];
                if ($stock['type'] == 'discount' && $stock['discount_algorithm'] == 'standart' && $stock['discount_value'] > 0 && $this->checkPromoCode($stock)) {
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

    public function orderActionCreate($params) {
        if (!($route_settings = self::isEnabled())) {
            return;
        }

        $add_gift_flag = false;
        $order_id = $params['order_id'];
        $contact_id = $params['contact_id'];
        $order_model = new shopOrderModel();
        $log_model = new shopOrderLogModel();

        $order = $order_model->getOrder($order_id);

        $product_ids = array();
        foreach ($order['items'] as $item) {
            if (!empty($item['product_id'])) {
                $product_ids[] = $item['product_id'];
            }
        }
        $stock_model = new shopStockPluginModel();
        $stocks = $stock_model->getStockByProducts($product_ids);
        if (!$stocks) {
            return;
        }

        $def_currency = wa('shop')->getConfig()->getCurrency(true);
        $product_percent_bonus = 0;

        foreach ($order['items'] as $item) {
            if (empty($item['product_id']) || empty($stocks[$item['product_id']])) {
                continue;
            }
            $stock = $stocks[$item['product_id']];
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

        $stock_absolute_bonus = 0;
        foreach ($order['items'] as $item) {
            if (!empty($item['product_id']) && !empty($stocks[$item['product_id']])) {
                $stock = $stocks[$item['product_id']];
                if ($stock['type'] == 'bonus' && $stock['absolute_bonus']) {
                    $stock_absolute_bonus = $stock['absolute_bonus'];
                    break;
                }
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

    public function routing($route = array()) {
        if (!($route_settings = self::isEnabled()) || empty($route_settings['stock_page']) || empty($route_settings['page_url'])) {
            return;
        }
        return array(
            $route_settings['page_url'] => 'frontend/stockList',
            $route_settings['page_url'] . '<stock>/' => 'frontend/stock',
        );
    }

    public function productsCollection($params) {
        if (!$this->getSettings('status')) {
            return false;
        }
        $collection = $params['collection'];
        $hash = $collection->getHash();
        if ($hash[0] !== 'stock') {
            return false;
        }
        $stock_id = $hash[1];

        $collection->addWhere("`id` IN (SELECT `product_id` FROM `shop_stock_plugin_products_join` WHERE `stock_id` = '" . (int) $stock_id . "')");
        return true;
    }

    public function sitemap($route) {
        if (!($route_settings = self::isEnabled()) || empty($route_settings['stock_page']) || empty($route_settings['page_url'])) {
            return;
        }
        $urls = array();

        $urls[] = array(
            'loc' => wa()->getRouteUrl('shop/frontend/stockList', true),
            'changefreq' => waSitemapConfig::CHANGE_MONTHLY,
            'priority' => 0.2
        );
        $stock_model = new shopStockPluginModel();
        $stocks = $stock_model->getActiveStocks($route_settings['sort']);
        foreach ($stocks as $stock) {
            $urls[] = array(
                'loc' => wa()->getRouteUrl('shop/frontend/stock', array('stock' => $stock['page_url']), true),
                'changefreq' => waSitemapConfig::CHANGE_MONTHLY,
                'priority' => 0.2
            );
        }
        return $urls;
    }

    public function stockInfo($product_id) {
        return false;
    }

    public static function shortList() {
        return false;
    }

}
