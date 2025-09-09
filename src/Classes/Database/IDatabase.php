<?php

namespace Classes\Database;

interface IDatabase {
    public function query($statement);
    public function execute($statement);
    public function prepare($statement, $options = []);

    public function getPDO();
    public function loadContents();
    public function databaseExists();
    public function createDatabase();
}
?>