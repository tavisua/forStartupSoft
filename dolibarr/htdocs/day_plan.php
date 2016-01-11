<?php

require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

//echo '<pre>';
//var_dump($_SERVER);
//echo '</pre>';
//die();

llxHeader("",$langs->trans('PlanOfDays'),"");
print_fiche_titre($langs->trans('PlanOfDays'));
$table = ShowTable();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/day_plan.html';
//print '</br>';
//print'<div style="float: left">test</div>';
llxFooter();

exit();

function ShowTable(){
    global $db, $user;
    $sql = 'select distinct `regions`.rowid, `regions`.name regions_name, states.name states_name
    from `regions`
    inner join (select fk_id from `llx_user_regions`';
    if(!$user->admin)
        $sql .='where fk_user = '.$user->id.' ';
    else
        $sql .='where 1 ';
    $sql .='and llx_user_regions.active = 1) as active_regions on active_regions.fk_id = `regions`.rowid
    left join states on `regions`.`state_id` = `states`.rowid
    where `regions`.active = 1
    order by states_name, `regions`.name';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $table = '<tbody id="reference_body">';
    while($obj = $db->fetch_object($res)){
        $table.='<tr>
            <td class="middle_size">'.$obj->states_name.'</td>
            <td class="middle_size" style="width:147px">'.str_replace('-', '- ',$obj->regions_name).'</td>
            </tr>';
    }
    $table .= '</tbody>';
    return $table;
}