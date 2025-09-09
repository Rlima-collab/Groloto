<?php

namespace Classes\Database;

use PDO;
use PDOException;

class MySQLDatabase extends Database
{
    private $db_name;
    private $db_user;
    private $db_pass;
    private $db_host;
    private $pdo;

    public function __construct($db_name, $db_user = 'root', $db_pass = 'root', $db_host = 'localhost') {
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->db_host = $db_host;

        $this->getPDO();
        $this->loadContents();
    }

    public function getPDO() {
        if ($this->pdo === null) {
            try {
                $this->pdo = new PDO(
                    "mysql:dbname={$this->db_name};host={$this->db_host};charset=utf8mb4",
                    $this->db_user,
                    $this->db_pass
                );
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo 'Connexion à la BD échouée : ' . $e->getMessage();
            }
        }
        
        return $this->pdo;
    }

    public function databaseExists() {
        $query = $this->query("SHOW TABLES LIKE 'QUIZ'");
        $result = $query->fetch();
        return $result;
    }

}

?>