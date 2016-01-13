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
    global $db, $user, $conf;
    $future_actions = array();
    //Майбутні дії
    for($i=0; $i<9; $i++) {
        $sql = "select `llx_societe`.`region_id`, count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where type in ('system','user') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        where 1 ";
        if($i<8) {
            $query_date = date("Y-m-d", (time()+3600*24*$i));
            $sql .= " and datep2 between '".$query_date . "' and date_add('" . $query_date . "', interval 1 day)";
        }else {
            $month = date("m");
            if($month+1<10)
                $month = '0'.($month+1);
            else
                $month =($month+1);
                $sql .= " and datep2 between '" . date("Y-m-d") . "' and '" . date("Y") . "-" . $month . "-" . (date("d")) . "'";
        }
        $sql .=" and datea is null
        group by `llx_societe`.`region_id`";

        $res = $db->query($sql);
        while($res && $obj = $db->fetch_object($res)){
            if($i<8)
                $future_actions[$obj->region_id.'_'.($i)]=$obj->iCount;
            else
                $future_actions[$obj->region_id.'_month']=$obj->iCount;
        }
    }
    //Прострочені дії
    $outstanding_actions = array();
    $sql = "select `llx_societe`.`region_id`, count(*) as iCount  from `llx_actioncomm`
    inner join
    (select id from `llx_c_actioncomm` where type in ('system','user') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
    left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
    where 1";
    $sql .= " and datep2 < '".date("Y-m-d")."'";
    $sql .=" and datea is null
    group by `llx_societe`.`region_id`";
    $res = $db->query($sql);
    while($obj = $db->fetch_object($res)){
        $outstanding_actions[$obj->region_id]=$obj->iCount;
    }


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
    $nom=0;

    while($obj = $db->fetch_object($res)){
        $class=(fmod($nom++,2)==0?"impair":"pair");
        $table.='<tr id = "'.$obj->rowid.'" class="'.$class.'">
            <td class="middle_size" style="width:106px">'.$obj->states_name.'</td>
            <td class="middle_size" style="width:146px">'.str_replace('-', '- ',$obj->regions_name).'</td>';
            //% виконання запланованого по факту

            for($i=0; $i<9; $i++){
                if($i == 0)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(34):(35)).'px"></td>';
                elseif($i == 8)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(32):(33)).'px"></td>';
                else
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(31)).'px"></td>';
            }
            //минуле (факт)
            for($i=0; $i<9; $i++){
                if($i == 0)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(34):(35)).'px"></td>';
                elseif($i == 8)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(32):(33)).'px"></td>';
                else
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(31)).'px"></td>';
            }
            //Прострочено сьогодні
            $id = "'#outstand".$obj->rowid."'";
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px"> <a  id = "outstand'.$obj->rowid.'" onclick="ShowTask($('.$id.'));" class="link">'.$outstanding_actions[$obj->rowid].'</a></td>';
            //майбутнє (план)
            for($i=0; $i<9; $i++){
                if($i == 0)
                    $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(33)).'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date='.date("Y-m-d").'">'.$future_actions[$obj->rowid.'_'.$i].'</a></td>';
                elseif($i == 8)
                    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(34)).'px">'.$future_actions[$obj->rowid.'_month'].'</td>';
                else {
                    $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (31) : (31)) . 'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date='.date("Y-m-d", (time()+3600*24*$i)).'">' . $future_actions[$obj->rowid . '_' . $i] . '</a></td>';
                }
            }
        $table.='</tr>';
    }
    $table .= '</tbody>';
    return $table;
}