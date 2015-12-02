<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 07.11.2015
 * Time: 11:32
 */
 $region_id = $_SESSION['region_id'];
//var_dump($_SESSION['region_id']);
//die();

$sql = 'select `llx_societe`.rowid, `llx_societe`.nom,
`llx_societe`.`town`, `llx_societe_classificator`.`value` as width, `llx_societe`.`remark`
from `llx_societe` left join `category_counterparty` on `llx_societe`.`categoryofcustomer_id` = `category_counterparty`.rowid
left join `formofgavernment` on `llx_societe`.`formofgoverment_id` = `formofgavernment`.rowid
left join `llx_societe_classificator` on `llx_societe`.rowid = `llx_societe_classificator`.`soc_id`';
if($region_id != 0) {
    $sql .= 'where `region_id` = ' . $region_id . ' ';
    $sql .= 'and `llx_societe`.`categoryofcustomer_id` in
(select responsibility_param.fx_category_counterparty from responsibility_param  where fx_responsibility = '.$user->respon_id.')';
}else
    $sql .= 'where 1 ';

$sql .= 'order by width desc, nom';
//var_dump($sql);
$TableParam = array();
$ColParam['title']='';
$ColParam['width']='180';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='130';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='80';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='180';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename = "`llx_societe`";
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db_mysql = new dbBuilder();

$table = $db_mysql->fShowTable($TableParam, $sql, "'" . $tablename . "'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder'], $readonly = array(-1), false);

//$row = $db_mysql->fShowTable($TableParam, $sql, "'" . $tablename . "'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder'], $readonly = array(-1), false);

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/customers.html');
return;