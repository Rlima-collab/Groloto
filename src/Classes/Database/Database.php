<?php

namespace Classes\Database;

abstract class Database implements IDatabase
{

    public function query($statement) {
        $pdo = $this->getPDO();
        return $pdo->query($statement);
    }

    public function execute($statement) {
        $pdo = $this->getPDO();
        return $pdo->exec($statement);
    }

    public function prepare($statement, $options = []) {
        $pdo = $this->getPDO();
        return $pdo->prepare($statement, $options);
    }

    public function loadContents() {
        if (!$this->databaseExists()) {
            $this->createDatabase();
        }
    }

    public function createDatabase() {
        $this->execute(file_get_contents(ROOT . '/Data/grosLoto.sql'));
        //$this->execute(file_get_contents(ROOT . '/Data/delGrosLoto.sql'));
    }
}