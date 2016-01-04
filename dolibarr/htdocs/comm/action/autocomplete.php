<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 02.01.2016
 * Time: 17:47
 */
global $db;
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
$sql="SELECT name FROM llx_c_kinddoc where name like '%".trim($_GET["term"])."%' order by name";
$query = $db->query($sql);
if($db->num_rows($query) == 0)
    echo '';

//строим массив результата/ы
for ($x = 0, $numrows = $db->num_rows($query); $x < $numrows; $x++) {
    $row = $query->fetch_assoc();
//    $row = mysql_fetch_assoc($query);
        $kinddoc[$x] = array("name" => $row["name"]);
}

//Выводим JSON на страницу
$response = $_GET["callback"] . "(" . json_encode($kinddoc) . ")";
echo $response;