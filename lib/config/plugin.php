<?php

return array(
    'name' => 'Акции',
    'description' => 'Возможность организовать акцию на сайте',
    'vendor' => '985310',
    'version' => '1.0.1',
    'img' => 'img/stock.png',
    'frontend' => true,
    'shop_settings' => true,
    'handlers' => array(
        'backend_product' => 'backendProduct',
        'frontend_product' => 'frontendProduct',
        'order_calculate_discount' => 'orderCalculateDiscount',
        'frontend_nav' => 'frontendNav',
        'frontend_cart' => 'frontendCart',
        'order_action.create' => 'orderActionCreate',
    ),
);
