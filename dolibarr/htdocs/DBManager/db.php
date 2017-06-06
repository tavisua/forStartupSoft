<?php
/**
 * Created by PhpStorm.
 * User: tavis
 * Date: 30.09.2015
 * Time: 15:46
 */

class dbMysqli {
    public $mysqli;
    private $baseLink;
    public function __construct()
    {
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/conf/conf.php')) {
            die('Не удалось найти файл конфигурации!');
        }
        else
            $config = include $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/conf/conf.php';
        global $dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass;
        $server = $dolibarr_main_db_host;
        $username = $dolibarr_main_db_user;
        $password = $dolibarr_main_db_pass;
        $db = $dolibarr_main_db_name;

        $port = '3306';
        $charset = 'utf8';
        $mysqli = new mysqli($server, $username, $password, $db, $port);
        if ($mysqli->connect_errno) {
            throw new Exception ("Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
        }

        $mysqli->set_charset($charset);
        $this->mysqli = $mysqli;

    }
}


