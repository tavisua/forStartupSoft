<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 07.11.2015
 * Time: 11:32
 */
 $region_id = $_SESSION['region_id'];
//var_dump('customer region_id='.$region_id);
//die();
$sql = 'select `llx_societe`.rowid, `category_counterparty`.name as category_name, `llx_societe`.`holding`, `llx_societe`.nom, `formofgavernment`.name as goverment_name,
`llx_societe`.`town`, `llx_societe`.`founder`, `llx_societe`.`phone`, `llx_societe_classificator`.`value` as width, `llx_societe`.`remark`
from `llx_societe` left join `category_counterparty` on `llx_societe`.`categoryofcustomer_id` = `category_counterparty`.rowid
left join `formofgavernment` on `llx_societe`.`formofgoverment_id` = `formofgavernment`.rowid
left join `llx_societe_classificator` on `llx_societe`.rowid = `llx_societe_classificator`.`soc_id`
where `region_id` = '.$region_id.'
order by width desc';

$TableParam = array();
$ColParam['title']='';
$ColParam['width']='130';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='150';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='180';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='80';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='130';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='130';
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
$table = '';
for($i=0; $i<50; $i++) {
    $row = $db_mysql->fShowTable($TableParam, $sql, "'" . $tablename . "'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder'], $readonly = array(-1), false);
    $row = substr($row, strpos($row, '</tr>')+6);

//    die('</br></br></br></br>'.htmlspecialchars(substr($row, 0, strpos($row, '</tbody>'))));
//    var_dump('</br></br></br></br>'.htmlspecialchars(substr($row, strpos($row, '</tr>')+6)));
//    die();
    $row = substr($row, 0, strpos($row, '</tbody>'));
    $table .= $row;

}
//$row = $db_mysql->fShowTable($TableParam, $sql, "'" . $tablename . "'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder'], $readonly = array(-1), false);
$table .= '</tbody>'."\r\n";
$table .= '</table>'."\r\n";

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/customers.html');
return;