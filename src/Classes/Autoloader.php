<?php

namespace Classes;

/**
 * Class Autoloader
 */
class Autoloader{

    /**
     * Enregistre notre autoloader
     */
    static function register(){
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Inclue le fichier correspondant à notre classe
     */
    static function autoload($fqcn){
        $path = str_replace('\\', '/', $fqcn);
        //require __DIR__ . '/' . $path . '.php';
        require ROOT . '/' . $path . '.php';
    }

}