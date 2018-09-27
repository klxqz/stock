<?php

return array(
    'name' => 'Акции',
    'description' => 'Возможность организовать акцию на сайте',
    'vendor' => '985310',
    'version' => '4.0.1',
    'img' => 'img/stock.png',
    'frontend' => true,
    'shop_settings' => true,
    'handlers' => array(
        'backend_products' => 'backendProducts',
        'frontend_product' => 'frontendProduct',
        'frontend_products' => 'frontendProducts',
        'frontend_cart' => 'frontendCart',
        'order_action.create' => 'orderActionCreate',
        'frontend_category' => 'frontendCategory',
        'frontend_homepage' => 'frontendHomepage',
        'routing' => 'routing',
        'products_collection' => 'productsCollection',
        'sitemap' => 'sitemap',
        'order_calculate_discount' => 'orderCalculateDiscount',
    ),
);
