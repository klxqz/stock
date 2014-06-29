<?php
return array(
    'shop_stockplugin' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'product_id' => array('int', 11, 'null' => 0),
        'name' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'description' => array('text', 'null' => 0),
        'date_begin' => array('date', 'null' => 0),
        'date_end' => array('date', 'null' => 0),
        'count' => array('int', 11, 'null' => 0),
        'type' => array('varchar', 20, 'null' => 0, 'default' => ''),
        'discount_type' => array('varchar', 20, 'null' => 0, 'default' => ''),
        'percent_discount' => array('float', 11, 'null' => 0),
        'new_price' => array('decimal', "15,4", 'null' => 0),
        'sku_gift' => array('varchar', 255, 'null' => 0, 'default' => ''),
        ':keys' => array(
            'PRIMARY' => array('id'),
            'product_id' => 'product_id',
            'date_begin' => 'date_begin',
            'date_end' => 'date_end',
        ),
    ),
);
