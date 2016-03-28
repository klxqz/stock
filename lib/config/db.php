<?php

return array(
    'shop_stock_plugin' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'enabled' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        'name' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'description' => array('text', 'null' => 0),
        'homepage' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        'datetime_begin' => array('datetime', 'null' => 0),
        'datetime_end' => array('datetime', 'null' => 0),
        'type' => array('enum', "'discount','gift'", 'null' => 0, 'default' => 'discount'),
        'discount_type' => array('enum', "'percent','absolute','price'", 'null' => 0, 'default' => 'percent'),
        'discount_value' => array('decimal', "15,4", 'null' => 0, 'default' => '0'),
        'gift_sku_id' => array('int', 11, 'null' => 0),
        'page_name' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'page_url' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'page_title' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'meta_keywords' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'meta_description' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'page_content' => array('text', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('id'),
            'datetime_begin' => 'datetime_begin',
            'datetime_end' => 'datetime_end',
        ),
    ),
    'shop_stock_products_plugin' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'stock_id' => array('int', 11, 'null' => 0),
        'type' => array('enum', "'product','set','category','type'", 'null' => 0, 'default' => 'product'),
        'value' => array('varchar', 64, 'null' => 0, 'default' => ''),
        ':keys' => array(
            'PRIMARY' => array('id'),
            'stock_id' => 'stock_id',
        ),
    ),
);
