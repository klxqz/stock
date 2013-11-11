<?php

return array(
    'status' => array(
        'title' => 'Статус',
        'description' => '',
        'value' => '1',
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            '0' => 'Выключен',
            '1' => 'Включен',

        )
    ),

    'default_output' => array(
        'title' => 'Вывод по умолчанию',
        'description' => 'Вывод короткого списка акций в стандартном месте',
        'value' => '1',
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            '0' => 'Выключен',
            '1' => 'Включен',
        )
    ),
    
    'frontend_product' => array(
        'title' => 'Вывод информации об акции в карточке товара',
        'description' => '',
        'value' => '1',
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            '0' => 'Выключен',
            '1' => 'Включен',
        )
    ),
    
    'frontend_product_output' => array(
        'title' => 'Место вывода',
        'description' => '',
        'value' => 'block',
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            'cart' => 'Содержимое, добавляемое рядом с кнопкой «В корзину».',
            'block_aux' => 'Блок дополнительной информации в боковой части страницы.',
            'block' => 'Блок дополнительной информации в основной части описания товара.',
        )
    ),
    
    'page_title' => array(
        'title' => 'Заголовок страницы «Акции»',
        'description' => '',
        'value' => 'Акции',
        'control_type' => waHtmlControl::INPUT,
    ),

);