<?php

class shopStockPluginImageDeleteController extends waJsonController {

    public function execute() {
        try {
            if ($img_name = waRequest::post('img')) {
                $image_path = wa()->getDataPath('/plugins/stock/' . $img_name, true, 'shop');
                unlink($image_path);
            } else {
                throw new waException('Имя файла не задано');
            }
        } catch (Exception $ex) {
            $this->setError($ex->getMessage());
        }
    }

}
