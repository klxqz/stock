<?php

class shopStockPluginBackendUploadController extends waJsonController {

    public function execute() {
        try {
            $file = waRequest::file('files');
            if ($file->uploaded()) {
                $file->waImage();
                $image_path = wa()->getDataPath('/plugins/stock/', true, 'shop');
                $path_info = pathinfo($file->name);
                $name = $this->uniqueName($image_path, $path_info['extension']);
                $file->moveTo($image_path . $name);

                $stock = waRequest::post('stock');
                if ($stock['img']) {
                    @unlink($image_path . $stock['img']);
                }

                if ($stock['id']) {
                    $stock_model = new shopStockPluginModel();
                    $stock_model->updateById($stock['id'], array('img' => $name));
                }

                $this->response['img'] = $name;
                $this->response['img_url'] = wa()->getDataUrl('/plugins/stock/' . $name, true, 'shop');
            } else {
                throw new waException('Ошибка загрузки файла');
            }
        } catch (Exception $ex) {
            $this->setError($ex->getMessage());
        }
    }

    protected function uniqueName($path, $extension) {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        do {
            $name = '';
            for ($i = 0; $i < 10; $i++) {
                $n = rand(0, strlen($alphabet) - 1);
                $name .= $alphabet{$n};
            }
            $name .= '.' . $extension;
        } while (file_exists($path . $name));

        return $name;
    }

}
