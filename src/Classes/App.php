<?php

use Config\ConfigBD;
use Classes\Database\SQLiteDatabase;
use Classes\Autoloader;

class App {

    private $db;
    private static $app;

    public static function getApp() {
        if (is_null(self::$app)) {
            self::$app = new App();
            session_start();

            require ROOT . '/Classes/Autoloader.php';

            Autoloader::register();

            self::getApp()->getBD();
        }

        return self::$app;
    }

    public function getBD() {
        if ($this->db === null) {
            $this->db = new SQLiteDatabase(ConfigBD::$DB_FILE);
            $this->db->loadContents();
        }
        return $this->db;
    }
}

?>