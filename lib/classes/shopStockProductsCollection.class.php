<?php

class shopStockProductsCollection extends shopProductsCollection
{
    public function stockFilter()
    {
        if ($this->filtered) {
            return;
        }
        
        $stock_model = new shopStockPluginModel();
        $stocks = $stock_model->getActiveStock();
        if(!$stocks) {
            return false;
        }
        $ids = array();
        
        foreach($stocks as $stock) {
            $ids[] = $stock['product_id'];
        }
        $this->where[] = 'p.id IN ('.  implode(',', $ids).')';
        
        
        $this->filtered = true;
        return true;
    }
    
}