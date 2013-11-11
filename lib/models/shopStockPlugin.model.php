<?php


class shopStockPluginModel extends waModel
{
    protected $table = 'shop_stockplugin';
    
    public function getActiveStock($key = null, $normalize = false)
    {
        $now = waDateTime::date("Y-m-d", null, wa()->getUser()->getTimezone());
        $sql = "SELECT * FROM {$this->table} WHERE `date_begin` <= '".$now."' AND `date_end` >= '".$now."'";
        return $this->query($sql)->fetchAll($key, $normalize);
    }
    
}

