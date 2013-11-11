<?php

class shopStockPlugin extends shopPlugin {

    protected static $plugin;

    public function __construct($info) {
        parent::__construct($info);
        if (!self::$plugin) {
            self::$plugin = &$this;
        }
    }

    protected static function getThisPlugin() {
        if (self::$plugin) {
            return self::$plugin;
        } else {
            return wa()->getPlugin('stock');
        }
    }

    public function backendProduct($product) {
        if ($this->getSettings('status')) {
            $view = wa()->getView();
            $view->assign('product', $product);
            $html = $view->fetch('plugins/stock/templates/BackendProduct.html');
            return array('edit_section_li' => $html);
        }
    }

    public function frontendNav() {

        if ($this->getSettings('status') && $this->getSettings('default_output')) {
            return self::shortList();
        }
    }

    public static function shortList() {
        $plugin = self::getThisPlugin();
        if ($plugin->getSettings('status')) {
            $stock_model = new shopStockPluginModel();
            $collection = new shopStockProductsCollection();
            $collection->stockFilter();
            $products = $collection->getProducts('*', 0, $plugin->getSettings('count'));
            foreach ($products as &$product) {
                $stock = $stock_model->getByField('product_id', $product['id']);
                $product['stock'] = $stock;
            }
            $view = wa()->getView();
            $view->assign('stock_products', $products);
            $html = $view->fetch('plugins/stock/templates/FrontendNav.html');
            return $html;
        }
    }

    public static function stockInfo($product_id) {
        $plugin = self::getThisPlugin();
        if ($plugin->getSettings('status')) {
            $stock_model = new shopStockPluginModel();
            $stock = $stock_model->getByField('product_id', $product_id);
            $view = wa()->getView();
            $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
            $time = strtotime($stock['date_end']) - strtotime($now);
            $view->assign('stock', $stock);
            $view->assign('time', $time);
            $template_path = wa()->getAppPath('plugins/stock/templates/StockInfo.html', 'shop');
            $html = $view->fetch($template_path);
            return $html;
        }
    }

    public function frontendProduct($product) {

        if ($this->getSettings('status') && $this->getSettings('frontend_product')) {

            $stock_model = new shopStockPluginModel();
            $stock = $stock_model->getByField('product_id', $product->id);
            if ($stock) {
                $view = wa()->getView();
                $now = waDateTime::date("Y-m-d H:i:s", null, wa()->getUser()->getTimezone());
                $time = strtotime($stock['date_end']) - strtotime($now);
                $view->assign('stock', $stock);
                $view->assign('time', $time);
                $html = $view->fetch('plugins/stock/templates/FrontendProduct.html');
                $frontend_product_output = $this->getSettings('frontend_product_output');
                return array($frontend_product_output => $html);
            }
        }
    }

    public function orderCalculateDiscount($params) {

        if ($this->getSettings('status')) {
            $stock_model = new shopStockPluginModel();

            foreach ($params['order']['items'] as $item) {
                $count = $item['quantity'];
                $stock = $stock_model->getByField('product_id', $item['product']['id']);

                if ($count < $stock['count']) {
                    continue;
                }

                if ($stock['type'] == 'discount' && $stock['discount_type'] == 'percent_discount') {
                    $percent_discount = $stock['percent_discount'];
                    $total = $item['price'] * $item['quantity'];
                    $total = shop_currency($total, null, null, false);
                    $discount = ceil($total * $percent_discount / 100);
                    return $discount;
                } elseif ($stock['type'] == 'discount' && $stock['discount_type'] == 'new_price') {
                    $new_total = $stock['new_price'] * $item['quantity'];
                    $total = shop_currency($new_total, null, null, false);
                    return $item['full_price'] - $new_total;
                } elseif ($stock['type'] == 'gift') {
                    $cart_model = new shopCartItemsModel();
                    $code = waRequest::cookie('shop_cart');
                    if (!$code) {
                        $code = md5(uniqid(time(), true));
                        wa()->getResponse()->setCookie('shop_cart', $code, time() + 30 * 86400, null, '', false, true);
                    }
                    $sku_model = new shopProductSkusModel();
                    $sku = $sku_model->getByField('sku', $stock['sku_gift']);
                    $session = wa()->getStorage();

                    if (!$session->read('stockplugin_' . $stock['sku_gift']) || !$cart_model->countSku($code, $sku['id'])) {
                        $data = array(
                            'sku_id' => $sku['id'],
                            'product_id' => $sku['product_id'],
                            'quantity' => 1,
                        );
                        $this->addToCart($data);
                        $session->write('stockplugin_' . $stock['sku_gift'], 1);
                        $redirect = wa()->getRouteUrl('/frontend/cart');
                        wa()->getResponse()->redirect($redirect);
                    }
                    $discount = shop_currency($sku['price'], null, null, false);
                    return $discount;
                }
            }
        }
    }

    protected function addToCart($data) {
        $cart_model = new shopCartItemsModel();
        $code = waRequest::cookie('shop_cart');
        if (!$code) {
            $code = md5(uniqid(time(), true));
            wa()->getResponse()->setCookie('shop_cart', $code, time() + 30 * 86400, null, '', false, true);
        }


        $sku_model = new shopProductSkusModel();
        $product_model = new shopProductModel();
        if (!isset($data['product_id'])) {
            $sku = $sku_model->getById($data['sku_id']);
            $product = $product_model->getById($sku['product_id']);
        } else {
            $product = $product_model->getById($data['product_id']);
            if (isset($data['sku_id'])) {
                $sku = $sku_model->getById($data['sku_id']);
            } else {
                if (isset($data['features'])) {
                    $product_features_model = new shopProductFeaturesModel();
                    $sku_id = $product_features_model->getSkuByFeatures($product['id'], $data['features']);
                    if ($sku_id) {
                        $sku = $sku_model->getById($sku_id);
                    } else {
                        $sku = null;
                    }
                } else {
                    $sku = $sku_model->getById($product['sku_id']);
                    if (!$sku['available']) {
                        $sku = $sku_model->getByField(array('product_id' => $product['id'], 'available' => 1));
                    }

                    if (!$sku) {
                        return false;
                    }
                }
            }
        }

        $quantity = $data['quantity'];

        if ($product && $sku) {
            // check quantity
            if (!wa()->getSetting('ignore_stock_count')) {
                $c = $cart_model->countSku($code, $sku['id']);
                if ($sku['count'] !== null && $c + $quantity > $sku['count']) {
                    $quantity = $sku['count'] - $c;
                    if (!$quantity) {
                        return false;
                    } else {
                        return false;
                    }
                }
            }
            $services = array();
            $item_id = null;
            $item = $cart_model->getItemByProductAndServices($code, $product['id'], $sku['id'], $services);
            if ($item) {
                $item_id = $item['id'];
                $cart_model->updateById($item_id, array('quantity' => $item['quantity'] + $quantity));
                if ($services) {
                    $cart_model->updateByField('parent_id', $item_id, array('quantity' => $item['quantity'] + $quantity));
                }
            }
            if (!$item_id) {
                $data = array(
                    'code' => $code,
                    'contact_id' => wa()->getUser()->getId(),
                    'product_id' => $product['id'],
                    'sku_id' => $sku['id'],
                    'create_datetime' => date('Y-m-d H:i:s'),
                    'quantity' => $quantity
                );
                $item_id = $cart_model->insert($data + array('type' => 'product'));
                if ($services) {
                    foreach ($services as $service_id => $variant_id) {
                        $data_service = array(
                            'service_id' => $service_id,
                            'service_variant_id' => $variant_id,
                            'type' => 'service',
                            'parent_id' => $item_id
                        );
                        $cart_model->insert($data + $data_service);
                    }
                }
            }
            // update shop cart session data
            $shop_cart = new shopCart();
            wa()->getStorage()->remove('shop/cart');
            return true;
        } else {
            return false;
        }
    }

}
