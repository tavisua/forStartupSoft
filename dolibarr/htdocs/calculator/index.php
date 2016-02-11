<?php

require '../main.inc.php';

if($_GET['action'] == 'set'){
    global $db;
    $sql = 'select region_id from llx_societe where rowid = '.$_GET['socid'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $region_id = $obj->region_id;
    $sql = 'select rowid from `calculator_parameters` where fk_calc = '.$_GET['theme_id'].' and fk_soc = '.$_GET['socid'].' limit 1';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    if(!$db->num_rows($res)){
        $sql = 'insert into `calculator_parameters`(fk_soc,fk_region,fk_calc,value,id_usr)
        values('.$_GET['socid'].',
               '.$region_id.',
               '.$_GET['theme_id'].',
               '.$_GET['val'].',
               '.$_GET['id_usr'].')';
    }else{
        $obj = $db->fetch_object($res);
        $sql = 'update `calculator_parameters` set
          fk_soc    = '.$_GET['socid'].',
          fk_region = '.$region_id.',
          fk_calc   = '.$_GET['theme_id'].',
          `value`   = '.(empty($_GET['val'])?'null':$_GET['val']).',
          id_usr    = '.$_GET['id_usr'].'
          where rowid = '.$obj->rowid;
    }
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    exit();
}

global $langs, $db;
$Calculator = $langs->trans("Calculator");

llxHeader("",$Calculator,"");
print_fiche_titre($Calculator);
$table = ShowTable();
//Теми калькулятора
$sql = "select `calculator_theme`.`rowid`, `calculator_theme`.`theme` from `calculator_theme`
    where `calculator_theme`.`respon_id` = ".$user->respon_id."
    and `calculator_theme`.`active` = 1
    order by dtChange desc";
$res = $db->query($sql);
if(!$res){
    dol_print_error($db);
}
$theme_header = '';
while($obj = $db->fetch_object($res)) {
    $theme_header .= '<th style="width: 50px" id = "'.$obj->rowid.'" class="small_size">'.$obj->theme.'</th>';
}

include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/calculator/index.html';
//print '</br>';
//print'<div style="float: left">test</div>';
llxFooter();

exit();

function ShowTable()
{
    global $db, $user;
    //Завантажую ИД теми
    $calc_theme=array();
    $sql = "select `calculator_theme`.`rowid` from `calculator_theme`
    where `calculator_theme`.`respon_id` = ".$user->respon_id."
    and `calculator_theme`.`active` = 1
    order by `calculator_theme`.`dtChange` desc";
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    while($obj = $db->fetch_object($res)){
        $calc_theme[]=$obj->rowid;
    }
    //Завантажую введені дані
    $calc_param=array();
    $sql = "select `calculator_theme`.`rowid`, `calculator_parameters`.`fk_region`,  SUM(`calculator_parameters`.`value`) dSum from `calculator_parameters`
        inner join `calculator_theme` on `calculator_theme`.`rowid` = `calculator_parameters`.`fk_calc`
        where `calculator_theme`.`respon_id` = ".$user->respon_id."
        and `calculator_parameters`.`value` is not null
        group by `calculator_theme`.`rowid`, `calculator_parameters`.`fk_region`";
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    while($obj = $db->fetch_object($res)){
        $calc_param[$obj->rowid.'_'.$obj->fk_region]=$obj->dSum;
    }
    $sql = 'select distinct `regions`.rowid, `regions`.name regions_name, states.name states_name
    from `regions`
    inner join (select fk_id from `llx_user_regions`';
    if ($user->login != "admin")
        $sql .= 'where fk_user = ' . $user->id . ' ';
    else
        $sql .= 'where 1 ';
    $sql .= 'and llx_user_regions.active = 1) as active_regions on active_regions.fk_id = `regions`.rowid
    left join states on `regions`.`state_id` = `states`.rowid
    where `regions`.active = 1
    order by states_name, `regions`.name';
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    $table = '<tbody id="reference_body">';
    $nom=0;
//    echo '<pre>';
//    var_dump($calc_param);
//    echo '</pre>';
//    die();
    $total = array();
    while($obj = $db->fetch_object($res)){
        $class=(fmod($nom++,2)==0?"impair":"pair");
        $table.='<tr id = "'.$obj->rowid.'" class="'.$class.'">
            <td class="middle_size" style="width:106px">'.$obj->states_name.'</td>
            <td class="middle_size" style="width:146px">'.str_replace('-', '- ',$obj->regions_name).'</td>';
        foreach($calc_theme as $theme_id){
            if(isset($calc_param[$theme_id.'_'.$obj->rowid])) {
                $table .= '<td id="calc_' . $theme_id . "_" . $obj->rowid . '" style="width: 51px">' . round($calc_param[$theme_id . "_" . $obj->rowid], 0) . '</td>';
                if(isset($total[$theme_id]))
                    $total[$theme_id]=$total[$theme_id]+round($calc_param[$theme_id . "_" . $obj->rowid], 0);
                else
                    $total[$theme_id]=round($calc_param[$theme_id . "_" . $obj->rowid], 0);
            }else
                $table .= '<td id="calc_'.$theme_id."_".$obj->rowid.'" style="width: 51px"></td>';
        }
        $table.='</tr>';
    }
    $table .= '</tbody>';
    foreach($total as $theme=>$value){
        $table .= '<input type="hidden"  value="'.$value.'" id="totaltheme'.$theme.'">';
//        var_dump($theme, $value);
//        die();
    }
    return $table;
}