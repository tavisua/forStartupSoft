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
$dbBuilder = new dbBuilder();
//var_dump($_GET);
//die();
if($_GET['tablename'] == 'kindofcustomer')
    $sql="SELECT name FROM kindofcustomer where name like '%".trim($_GET["term"])."%' order by name";
elseif($_GET['tablename'] == 'llx_c_ziptown') {
    if($_GET['fieldname'] == 'nametown')
    $sql = 'SELECT llx_c_ziptown.rowid, llx_c_ziptown.fk_state, llx_c_ziptown.`fk_region`, trim(llx_c_ziptown.nametown) as nametown, concat(trim(nametown), " ", trim(regions.name), " р-н. ", trim(states.name), " обл.") as name
        FROM llx_c_ziptown
        left join states on states.rowid = llx_c_ziptown.fk_state
        left join regions on regions.rowid =  llx_c_ziptown.`fk_region`
        where trim(nametown) like "'.trim($_GET["term"]).'%" order by llx_c_ziptown.nametown, regions.name, states.name';
}elseif($_GET['tablename'] == 'llx_societe'){
    $sql = 'select llx_societe.rowid, llx_societe.nom as name from llx_societe
    where categoryofcustomer_id = '.$_GET['categoryofcustomer_id'].
    ' and trim(nom) like "'.trim($_GET["term"]).'%" and active = 1 order by llx_societe.nom';
}

//die($sql);

$query = $db->mysqli->query($sql);
if($query->num_rows == 0)
    echo '';

//строим массив результата/ы
for ($x = 0, $numrows = $query->num_rows; $x < $numrows; $x++) {
    $row = $query->fetch_assoc();
//    $row = mysql_fetch_assoc($query);
    if($_GET['tablename'] == 'kindofcustomer')
        $friends[$x] = array("name" => $row["name"]);
    elseif($_GET['tablename'] == 'llx_c_ziptown') {
        $friends[$x] = array("rowid" => $row["rowid"], "name" => $row["name"], "state_id"=>$row["fk_state"], "region_id"=>$row["fk_region"]);
    }elseif($_GET['tablename'] == 'llx_societe'){
        $friends[$x] = array("rowid" => $row["rowid"], "name" => $row["name"]);
    }
}
//echo '<pre>';
//var_dump($friends);
//echo '</pre>';
//die();
//Выводим JSON на страницу
$response = $_GET["callback"] . "(" . json_encode($friends) . ")";
echo $response;
//var_dump($_GET);