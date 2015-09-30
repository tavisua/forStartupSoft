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
////        include '/dolibarr/htdocs/conf/conf.php';
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/conf/conf.php')) {
            die('Не удалось найти файл конфигурации!');
        }
        else
            $config = include $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/conf/conf.php';
//        var_dump($dolibarr_main_db_host);
//        die();
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

    public function fShowTable($title = array(), $sql){
        $table ='<table width="100%" class="noborder">'."\r\n";
        $table .= '<tbody>'."\r\n";
        $table .= '<tr class="liste_titre">'."\r\n";
        foreach($title as $column){
            $table .= '<th ';
            $table .= $column['width']<>''?('width="'.$column['width'].'"'):(' ');//ширина
            $table .= $column['align']<>''?('align="'.$column['align'].'"'):(' ');//выравнивание
            $table .= $column['class']<>''?('class="'.$column['class'].'"'):(' ');//класс
            $table .= '>'.$column['title'].'</th>';
        }
        $table .= '</tr>'."\r\n";
        $result = $this->mysqli->query($sql);
//        $array = $result->fetch_array();
        $count = 0;
        while($row = $result->fetch_assoc()) {
            $count++;
            $class = fmod($count,2)==1?("impair"):("pair");
            $table .= "<tr id = $count class='".$class."'>\r\n";
//            $table .= "<tr id = $count class=".fmod($count,2)==1?('impair'):('pair').">\r\n";
            $id = $row['rowid'];
//            $table .= '<td >'.$class.'</td>';
            foreach($row as $cell=>$value){
                if($cell != 'rowid')
                    $table .= '<td >'.$value.'</td>';
            }
            $table .= '<td style="width: 50px" align="left">
                            <button onclick="edit_item('.$row['rowid'].');">
                                Редактировать
                            </button>
                            <button onclick="del_confirm('.$row['rowid'].');">
                                Удалить
                            </button>
                       </td>';
            $table .= '</tr>';
        }
        $table .= '</tbody>'."\r\n";
        $table .= '</table>';
        $table .= "<script>
                    function del_confirm(rowid) {
                        if (confirm('Удалить пункт меню?'))
                            location.href = 'http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?rowid='+rowid+'&del=1';
                    }
                    function edit_item(rowid){
                        location.href ='http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?rowid='+rowid+'&edit=1';
                    }
                    </script>";
        return $table;
    }

} 