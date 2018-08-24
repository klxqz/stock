<?php

class shopStockPluginRunCli extends waCliController {

    public function execute() {

        $argv = waRequest::server('argv');

        if (!empty($argv[3])) {
            list($text, $stock_id) = explode('=', $argv[3]);
        }
        $_POST['id'] = $stock_id;
        $runner = new shopStockPluginBackendUpdateController();
        ob_start();
        $runner->execute();
        $out = ob_get_clean();
        $result = json_decode($out, true);

        if ($result['status'] == 'ok') {
            echo 'OK';
        } else {
            echo implode(', ', $result['errors']);
        }
    }

}
