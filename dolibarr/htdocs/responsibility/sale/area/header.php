<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 05.11.2015
 * Time: 10:00
 */

$TitleToday = $langs->trans('TitleToday');
$Today = date('d.m.Y');
$Worker = $langs->trans('worker');
$State = $langs->trans('Region');
$Area = $langs->trans('Area');
$AsOfTheDate = $langs->trans('AsOfTheOfDate');
//die($TitleToday);
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//exit();
if(isset($_GET['id_usr'])&&!empty($_GET['id_usr'])) {
    $user->fetch($_GET['id_usr']);
}
$region_id = 0;
if(isset($_REQUEST['state_filter'])) {//Если изменялся регион
    if(substr($_REQUEST['state_filter'], 0, strlen('category_id_'))!='category_id_') {
        $region_id = $_REQUEST['state_filter'];
    }
//    var_dump(GETPOST('state_filter'), 'all');
}

$AreaList = '<form id="setStateFilter" action="'.$_SERVER["REQUEST_URI"].'" method="post">'.$user->getAreasList($region_id).'</form>';

//$region_id = 210;
$sql = "select `classifycation`.name, SUM(b.value) value from `classifycation` left join
(select `classifycation_id`, `value`
from `regions_param`";
if($region_id != 0)
    $sql .=" where `regions_param`.`regions_id` = ".$region_id.") b on `classifycation`.rowid = b.`classifycation_id`";
else
    $sql .=" where 1) b on `classifycation`.rowid = b.`classifycation_id`";
$sql .=" where `classifycation`.calc=0 and `classifycation`.active = 1 group by `classifycation`.name order by classifycation.rowid";
$res = $db->query($sql);
if(!$res){
    var_dump($sql);
    dol_print_error($db);
}

if($db->num_rows($res) > 0) {
    $Classifycation = '<table class="classifycation">';
    for ($i = 0; $i < $db->num_rows($res); $i++) {
        $obj=$db->fetch_object($res);
        $Classifycation .= '<tr><td>'.$obj->name.'</td><td class="autoinsert">'.round($obj->value).'</td></tr>';
        if($i == $db->num_rows($res)-1)
            $CalcValue = $obj->value;
    }
    //Визначаю загальну кількість земель у клієнтів в районі
        $sql = 'select `classifycation`.`name`,SUM(`statistic`.`value`) value from `classifycation` left join
    (select `llx_societe_classificator`.`classifycation_id`, `llx_societe_classificator`.value
    from `llx_societe`, `llx_societe_classificator`';
    if($region_id != 0)
        $sql .=' where `llx_societe`.region_id = '.$region_id;
    else
        $sql .=' where 1 ';
    $sql .=' and `llx_societe_classificator`.`soc_id` = `llx_societe`.rowid) statistic on `classifycation`.rowid = statistic.`classifycation_id`
    where `classifycation`.calc = 1 and `classifycation`.active = 1
    group by `classifycation`.`name`';
    $res = $db->query($sql);
    if(!$res){
        var_dump($sql);
        dol_print_error($db);
    }
    for ($i = 0; $i < $db->num_rows($res); $i++) {
        $obj=$db->fetch_object($res);
        $Classifycation .= '<tr><td>'.$obj->name.'</td><td class="autoinsert">'.round($obj->value).'</td></tr>';
        if($i == $db->num_rows($res)-1 && !empty($CalcValue)) {
            $CalcValue = ceil(($obj->value/$CalcValue)*100);
        }
    }
    $Classifycation .= '<tr><td>Пах. зем. клиентов к районной, %</td><td class="autoinsert">'.$CalcValue.'</td></tr>';
    $Classifycation .= '<tr><td>Кількість контр-тів</td><td class="autoinsert" id="iCount"></td></tr>';
    $Classifycation .='</table>';
}
$CreateCompany = $langs->trans('CreateCompany');
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/area/header.html');
return;
