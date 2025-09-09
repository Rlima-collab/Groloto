<?php

namespace Classes\Database;

use PDO;
use PDOException;

class SQLiteDatabase extends Database
{
    private $db_file;
    private $pdo;

    public function __construct($db_file) {
        $this->db_file = $db_file;

        $this->getPDO();
    }

    public function getPDO() {
        if ($this->pdo === null) {
            try {
                $this->pdo = new PDO("sqlite:{$this->db_file}");
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo 'Connexion à la BD échouée : ' . $e->getMessage();
            }
        }
        return $this->pdo;
    }

    public function databaseExists() {
        $query = $this->query("SELECT name FROM sqlite_master WHERE type='table' AND name='QUIZ'");
        $result = $query->fetch();
        return $result;
    }

}

?>