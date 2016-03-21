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
//var_dump($_SERVER["REQUEST_URI"]);
//echo '</pre>';
//die();
$sql = 'select regions.rowid, regions.state_id, trim(states.name) as states_name, trim(regions.name) as regions_name from states, regions, '.MAIN_DB_PREFIX.'user_regions ur
    where ur.fk_user='.$user->id.' and ur.active = 1 and ur.fk_id=regions.rowid and regions.state_id=states.rowid order by regions_name asc, states_name asc';
//die($sql);
$res = $db->query($sql);

$region_id = 0;

if(strlen(GETPOST('state_filter'))>0) {//Если изменялся регион
    $region_id = GETPOST('state_filter');
//    var_dump(GETPOST('state_filter'), 'all');
}
if($db->num_rows($res)>0) {
    $AreaList =  '<form action="'.$_SERVER["REQUEST_URI"].'" method="post"><select name = "state_filter" id="state_filter" class="combobox" onchange="this.form.submit()">';
    $AreaList .='<option value="0" class="multiple_header_table">Відобразити все</option>\r\n';
    for ($i = 0; $i < $db->num_rows($res); $i++) {
        $obj = $db->fetch_object($res);
//        if($region_id == 0) {
//            $region_id = $obj->rowid;
//        }
        $selected = $region_id == $obj->rowid;
        if(!$selected)
            $AreaList .= '<option value="'.$obj->rowid.'" >'.trim($obj->regions_name).' ('.decrease_word($obj->states_name).')</option>';
        else {
            $AreaList .= '<option value="' . $obj->rowid . '" selected = "selected" >' . trim($obj->regions_name) . ' (' . decrease_word($obj->states_name) . ')</option>';
            $state_id = $obj->state_id;
        }
    }
    $AreaList .= '</select></form>';
}

$_SESSION['region_id'] = $region_id;

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
    $Classifycation .='</table>';
}
$CreateCompany = $langs->trans('CreateCompany');

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/area/header.html');
return;

function decrease_word($text){

    $symbol_array= array('б','в','г','д','ж','з','к','л','м','н','п','р','с','т','ф','х','ц','ч','ш','щ');
    for($i=1; $i<strlen($text); $i++){
        if(in_array(mb_substr($text, $i, 1, 'UTF-8'), $symbol_array)){
            return mb_substr($text, 0, $i+1, 'UTF-8').'.';
        }
    }
    return ':(';
}