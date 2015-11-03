<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 03.11.2015
 * Time: 11:03
 */
//require '../dev/skeletons/build_class_from_table.php';
//var_dump(DOL_DOCUMENT_ROOT);


include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();

//if($_REQUEST['tablename'] == 'kindofcustomer')
    $sql="SELECT name FROM kindofcustomer where name like '%".trim($_GET["term"])."%' order by name";

$query = $db->mysqli->query($sql);
if($query->num_rows == 0)
    echo '';

//строим массив результата/ы
for ($x = 0, $numrows = $query->num_rows; $x < $numrows; $x++) {
    $row = $query->fetch_assoc();
//    $row = mysql_fetch_assoc($query);

    $friends[$x] = array("name" => $row["name"]);
}
//echo '<pre>';
//var_dump($friends);
//echo '</pre>';
//die();
//Выводим JSON на страницу
$response = $_GET["callback"] . "(" . json_encode($friends) . ")";
echo $response;