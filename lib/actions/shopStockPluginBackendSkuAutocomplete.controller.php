<?php

class shopStockPluginBackendSkuAutocompleteController extends waController {

    public function execute() {
        $data = array();
        $q = waRequest::get('term', '', waRequest::TYPE_STRING_TRIM);
        if ($q) {
            $data = $this->skusAutocomplete($q);
        }
        echo json_encode($data);
    }

    public function skusAutocomplete($q) {
        $model = new waModel();
        $q = $model->escape($q, 'like');
        $sql = "SELECT `sps`.`id`, `sps`.`product_id`, `sp`.`name`, `sps`.`sku`, `sps`.`name` as `sku_name`
                FROM `shop_product_skus` as `sps` 
                LEFT JOIN `shop_product` as `sp` 
                ON `sps`.`product_id` = `sp`.`id`
                WHERE `sps`.`name` LIKE '{$q}%' OR `sps`.`sku` LIKE '{$q}%' OR `sp`.`name` LIKE '%{$q}%' LIMIT 10";
        $skus = $model->query($sql)->fetchAll();
        foreach ($skus as &$sku) {
            $sku['value'] = "<strong>{$sku['name']}</strong>";
            if ($sku['sku'] && $sku['sku_name']) {
                $sku['value'] .= " <i style='color:gray;'>(<strong>{$sku['sku']}</strong>: {$sku['sku_name']})</i>";
            } elseif ($sku['sku']) {
                $sku['value'] .= " <i style='color:gray;'>(<strong>{$sku['sku']}</strong>)</i>";
            } elseif ($sku['sku_name']) {
                $sku['value'] .= " <i style='color:gray;'>({$sku['sku_name']})</i>";
            }
        }
        return $skus;
    }

}
