<?php

class shopStockPluginBackendSaveController extends waJsonController
{

    public function execute()
    {
        $stock_post = waRequest::post('shop_stock');
        $stock_model = new shopStockPluginModel();        
        if($stock_post['id']) {
            $stock_model->updateById($stock_post['id'],$stock_post);
        } else {
            $id = $stock_model->insert($stock_post);
            $this->response['id'] = $id;
        }
    }
    
}

