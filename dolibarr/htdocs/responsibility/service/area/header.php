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

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/service/area/header.html');
return;


function LineActive(){
    global $db, $user, $id_usr;
//    $sql = 'select `oc_category_description`.category_id, `oc_category_description`.name from `oc_category_description`
//            inner join
//                (select fk_lineactive as category_id from `llx_user_lineactive`
//                where fk_user = '.$user->id.'
//                and active = 1) lineactive on lineactive.category_id = `oc_category_description`.category_id
//            where `oc_category_description`.language_id = 4';
//    $sql.=" union
//    select 'users', 'Співробитники'  ";
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
//    $res = $db->query($sql);
//    if(!$res)
//        dol_print_error($db);
    require DOL_DOCUMENT_ROOT.'/core/lib/day_plan.php';
    $lineactive = getLineActive($id_usr);
//    echo '<pre>';
//    var_dump($lineactive);
//    echo '</pre>';
//    die();
    $out = '<select id="lineactive" class="combobox" onchange="setLineActiveFilter();">';
    $out.='<option value="-1" selected="selected">Відобразити всі</option>';
    $category_id = isset($_REQUEST['lineactive'])&& !empty($_REQUEST['lineactive'])?$_REQUEST['lineactive']:0;
//    while($obj = $db->fetch_object($res)){
//        $out.='<option '.($obj->category_id == 'users'?'id="users"':'').' value="'.$obj->category_id.'" '.(is_numeric($category_id) == is_numeric($obj->category_id) && $category_id == $obj->category_id?'selected="selected"':'').'>'.$obj->name.'</option>';
//    }
    foreach(array_keys($lineactive) as $key){
        $out.='<option '.($key == 'users'?'id="users"':'').' value="'.$key.'" '.(is_numeric($category_id) == is_numeric($key) && $category_id == $key?'selected="selected"':'').'>'.$lineactive[$key]['name'].' ['.$lineactive[$key]['type'].']</option>';
    }
    $out.='</selected>';
    return $out;
}