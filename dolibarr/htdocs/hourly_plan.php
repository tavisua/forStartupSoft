<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 09.12.2015
 * Time: 9:59
 */

require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

//echo '<pre>';
//var_dump($_REQUEST['hide'] == 'true');
//echo '</pre>';
//die();
if($_REQUEST['action'] == 'hideAction'){
    global $db;
    $hide = $_REQUEST['hide'] == 'true';
    $sql = "update llx_actioncomm set hide=".($hide?1:0)." where id=".$_REQUEST['action_id'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    echo 1;
    exit();
}

$HourlyPlan = $langs->trans('HourlyPlan');
llxHeader("",$HourlyPlan,"");
print_fiche_titre($langs->trans('HourlyPlan'));
$table = '<table class="WidthScroll" cellspacing="1" id="schedule_table">';
$table .= '<thead>
                <tr class="multiple_header_table">
                    <th width="58" rowspan="2">Години</th>
                    <th rowspan="2">Хвилини</th>
                    <th width="300px" colspan="10">Наявні завдання</th>
                </tr>
                <tr class="multiple_header_table">
                    <th class="small_size">Тип</th>
                    <th class="small_size">Початок</th>
                    <th class="small_size">Необх.</th>
                    <th class="small_size">Кінець</th>
                    <th class="small_size" width="150px">Направлення</th>
                    <th class="small_size" width="150px">Контрагент/замовник</th>
                    <th class="small_size" width="200px">Задача</th>
                    <th class="small_size" width="150px">Примітка: Що зробить, кінцева мета, підтвердження</th>
                    <th class="small_size" width="128px">Статус</th>
                    <th class="small_size" width="35"></th>
                </tr>
           </thead>';
$row = 0;
$hour = 0;
$split=array();
//global $conf;
//var_dump($conf->browser->name);
if(!isset($_REQUEST['date'])){
    $date = date('d.m.Y');
}else{
    $date = $_REQUEST['date'];
}
$dateQuery = new DateTime($date);
//var_dump($dateQuery->format('Y-m-d'));
//die();
$callstatus = array();
$sql = "select rowid, status from `llx_c_callstatus` where active = 1";
$callres = $db->query($sql);
if(!$callres)
    dol_print_error($db);
while($obj = $db->fetch_object($callres))
    $callstatus[$obj->rowid] = $obj->status;

$actionURL = '/comm/action/card.php';
if(!isset($_GET['id_usr'])||empty($_GET['id_usr']))
    $id_usr = $user->id;
elseif(!empty($_GET['id_usr']))
    $id_usr = $_GET['id_usr'];
$sql = "select  `llx_actioncomm`.type, `llx_actioncomm`.id as rowid, `llx_actioncomm`.datep, `llx_actioncomm`.datep2,
        `llx_actioncomm`.`code`, `llx_actioncomm`.fk_user_author, `llx_actioncomm`.label, `llx_societe`.region_id, `regions`.`name` as region_name, case when `llx_actioncomm`.fk_soc is null then `llx_user`.`lastname` else `llx_societe`.`nom` end lastname,
        `llx_actioncomm`.`note`, `llx_actioncomm`.`percent`, `llx_c_actioncomm`.`libelle` title, `llx_actioncomm`.confirmdoc,
        `llx_actioncomm`.priority, max(`llx_societe_action`.`callstatus`) as callstatus
        from `llx_actioncomm`
        left join `llx_societe` on `llx_societe`.rowid = `llx_actioncomm`.fk_soc
        left join `states` on `states`.rowid = `llx_societe`.state_id
        left join `regions` on `regions`.rowid=`llx_societe`.region_id
        left join `llx_user` on `llx_user`.rowid= `llx_actioncomm`.fk_user_author
        left join `llx_c_actioncomm` on `llx_c_actioncomm`.`code` = `llx_actioncomm`.`code`
        left join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        left join `llx_societe_action` on `llx_societe_action`.`action_id` = `llx_actioncomm`.`id`
        where fk_action in
              (select id from `llx_c_actioncomm`
              where `type` in ('system', 'user'))
        and case when `llx_actioncomm_resources`.`fk_element` is null then `llx_actioncomm`.fk_user_author else `llx_actioncomm_resources`.`fk_element` end = ".$id_usr."
        and date(datep) = '".$dateQuery->format('Y-m-d')."'
        and `llx_actioncomm`.active = 1
        and `llx_actioncomm`.percent not in(-100)
        and (`llx_actioncomm`.hide is null or `llx_actioncomm`.hide <> 1)
        group by `llx_actioncomm`.id,  `llx_actioncomm`.datep, `llx_actioncomm`.datep2,
        `llx_actioncomm`.`code`, `llx_actioncomm`.fk_user_author, `llx_actioncomm`.label, `regions`.`name`, case when `llx_actioncomm`.fk_soc is null then `llx_user`.`lastname` else `llx_societe`.`nom` end,
        `llx_actioncomm`.`note`, `llx_actioncomm`.`percent`, `llx_c_actioncomm`.`libelle`, `llx_actioncomm`.confirmdoc,
        `llx_actioncomm`.priority
        order by priority,datep";
$res = $db->query($sql);
//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//die();
//die($sql);
$task = '';

//$task.='<div id="currenttime"></div>';
$priority = "-100";
$task = '<div id="currenttime" style="z-index: 10;"></div>';
//$prev_time = mktime(8,0,0, $dateQuery->format('m'),$dateQuery->format('d'),$dateQuery->format('Y'));
//$emptyid=0;
$count = 0;
while($row = $db->fetch_object($res)) {
    if($row->priority != $priority) {
        $priority = $row->priority;
        $prev_time = mktime(0,0,0, $dateQuery->format('m'),$dateQuery->format('d'),$dateQuery->format('Y'));
        $emptyid=0;
//        var_dump($row->priority != $priority, $row->priority,  $priority);
//        die();
        if($row->priority != 0)
            $task .= '</tbody></table></div>';

        $task .='<div id = "tasklist'.$row->priority.'" style="z-index: '.$row->priority.';"><table  class="tasklist">
            <tbody id="tbody'.$priority.'">';
    }
//var_dump(htmlspecialchars($task));
//    die();
    switch (trim($row->code)) {
        case 'AC_GLOBAL': {
            $classitem = 'global_taskitem';
            $iconitem = 'object_global_task.png';
        }
            break;
        case 'AC_CURRENT': {
            $classitem = 'current_taskitem';
            $iconitem = 'object_current_task.png';
        }
            break;
        case 'AC_RDV': {
            $classitem = 'office_meetting_taskitem';
            $iconitem = 'object_office_meetting_task.png';
        }
            break;
        case 'AC_TEL': {
            $classitem = 'office_callphone_taskitem';
            $iconitem = 'object_call.png';
        }
            break;
        case 'AC_DEP': {
            $classitem = 'departure_taskitem';
            $iconitem = 'object_departure_task.png';
        }
            break;
    }
    if(trim($row->code) == 'AC_TEL'){
        if ($row->percent <= 0)
            $status = 'Не розпочато';
        else {
            $status = $callstatus[(empty($row->callstatus) ? 2 : $row->callstatus)];

//            if($row->rowid == 102923) {
//                var_dump($row->callstatus == null);
//                die();
//            }
            if($status == 'виконано')
                $count++;
        }
    }else {
        if ($row->percent == -1)
            $status = 'Не розпочато';
        elseif ($row->percent > 0 && $row->percent < 100)
            $status = 'В роботі(' . $row->percent . '%)';
        elseif ($row->percent == 0)
            $status = 'Тільки-но розпочато';
        else
            $status = 'Виконано';
    }
    $datep = new DateTime($row->datep);
    $datep2 = new DateTime($row->datep2);
    $DiffSec = (mktime($datep2->format('H'), $datep2->format('i'), $datep2->format('s'), $datep2->format('m'), $datep2->format('d'), $datep2->format('Y')) -
        mktime($datep->format('H'), $datep->format('i'), $datep->format('s'), $datep->format('m'), $datep->format('d'), $datep->format('Y')));
    $EmptyPeriod = (mktime($datep->format('H'), $datep->format('i'), $datep->format('s'), $datep->format('m'), $datep->format('d'), $datep->format('Y')) - $prev_time) / 60;
    if ($EmptyPeriod > 0) {
        $task .= '<tr id="empty' .$row->priority.($emptyid++) . '"><td class="emptyitem"></td></tr>';
//        $task.='<tr><td style="height: '.($EmptyPeriod*($conf->browser->name == 'firefox' ? ($EmptyPeriod<=30?23.9:24) : 22)/10).'px" class="emptyitem"></td></tr>';
    }
    $DiffTime = sprintf('%02d:%02d', $DiffSec / 3600, ($DiffSec % 3600) / 60, $DiffSec % 60);
    $taks = trim($row->note);
    $length = 30;
    if(mb_strlen($taks, 'UTF-8')>$length){
        $taks=mb_substr($taks, 0, $length, 'UTF-8').'...<input type="hidden" value="'.trim($row->note).'">';
    }
    $task_table = '<div class="task_cell" style="float: left; width: ' . ($conf->browser->name == 'firefox' ? '23px' : '24px') . '"><img src="theme/' . $conf->theme . '/img/' . $iconitem . '" title="' . $langs->trans($row->title) . '"></div>
           <div class="task_cell" style="float: left; width: ' . ($conf->browser->name == 'firefox' ? '42px' : '43px') . '">' . $datep->format('H:i') .
            (!empty($row->type)?'<span style="float: left;margin-left: -5px;z-index: 5"><img title="Час початку дії встановлено вручну" src="/dolibarr/htdocs/theme/eldy/img/object_task.png"></span>':'').'</div>
           <div class="task_cell" style="float: left; width: 36px; height 16px">' . $DiffTime . '</div>
           <div class="task_cell" style="float: left; width: 35px">' . $datep2->format('H:i') . '</div>
           <div class="task_cell" style="float: left; width: 152px">' . trim($row->region_name) .(!empty($row->region_name)?' район':'').'</div>
           <div class="task_cell" style="float: left; width: 152px">'. (mb_strlen(trim($row->lastname), 'UTF-8')>25?mb_substr(trim($row->lastname), 0,25,'UTF-8').'...':trim($row->lastname)) . '</div>
           <div class="task_cell note" style="float: left; width: 202px;">' . $taks . '</div>
           <div class="task_cell" style="float: left; width: 152px;">' .(mb_strlen(trim($row->confirmdoc), 'UTF-8')>25?mb_substr(trim($row->confirmdoc), 0,25,'UTF-8').'...':trim($row->confirmdoc)). '</div>
           <div class="task_cell" style="float: left; width: 130px;">' . $status . '</div>';
    if($user->id == $row->fk_user_author)
        $task_table .='<div class="task_cell" style="float: left; width: 20px; border-color: transparent"><img title="Редагувати дію" src="theme/eldy/img/edit.png">
            <!--<img onclick="HideAction('.$row->rowid.');" title="Скрити дію" src="theme/eldy/img/hide.png"></div>-->';
    else
        $task_table .='<div class="task_cell" style="float: left; width: 20px; border-color: transparent"></div>';

//    $task .= '<div id="'.$row->rowid.'" class="'.$classitem.'" style="height: 216px" >' . $task_table . '</div>';
    if(!empty($_GET['region_id']))
        $selected = $_GET['region_id'] == $row->region_id;
    $task .= '<tr id="' . $row->rowid . '"><td class="' . $classitem . ' '.($selected?"sel_item":"").'" >' . $task_table . '</td></tr>';
//    $task.='<tr id="'.$row->rowid.'"><td class="'.$classitem.'" style="height: '.($DiffSec/600*($conf->browser->name == 'firefox' ? ($DiffSec/60<=30?($DiffSec/60<15?22:23.8):23.7) : 22)).'px">'.$task_table.'</td></tr>';
    $prev_time = mktime($datep2->format('H'), $datep2->format('i'), $datep2->format('s'), $datep2->format('m'), $datep2->format('d'), $datep2->format('Y'));
}
//var_dump($count);
//die();
if($db->num_rows($res))
    $task.='    </tbody>
                </table></div>';

//$task = 'test';
$table .= '<tbody id="schedule_body">';
$totalcount = 144;
for($period=0; $period<$totalcount;$period++){
    if($db->num_rows($res)) {
        if ($row == 0) {
            $table .= '<tr><td rowspan="6" style="vertical-align: text-top" width="32px" ' . (($hour == 12 || $hour == 13) ? 'class="lanch"' : '') . '>' . $hour++ . 'год <td style="height: 51px" id="' . ($hour - 1) . 'h0m" width="54px" ' . (($hour - 1 == 12 || $hour - 1 == 13) ? 'class="lanch"' : '') . '>'.'00 хв.</td>' . ($period == 0 && $row == 0 ? '<td rowspan="'.$totalcount.'" height="100%" id="taskfield" valign="top" >' . $task . '</td>' : '');
        } else {
            $table .= '<tr><td style="height: 51px" id="' . ($hour - 1) . 'h' . ($row * 10) . 'm" ' . (($hour - 1 == 12 || $hour - 1 == 13) ? 'class="lanch"' : '') . '>' . ($row * 10) . 'хв.</td>';
        }
    }else{
        if ($row == 0) {
            $table .= '<tr><td rowspan="6" style="vertical-align: text-top" width="32px" ' . (($hour == 12 || $hour == 13) ? 'class="lanch"' : '') . '>' . $hour++ . 'год <td style="height: 51px" id="' . ($hour - 1) . 'h0m" width="54px" ' . (($hour - 1 == 12 || $hour - 1 == 13) ? 'class="lanch"' : '') . '>00 хв.</td>' . ($period == 0 && $row == 0 ? '<td rowspan="'.$totalcount.'" height="100%" width="100%" id="taskfield" valign="top" ></td>' : '');
        } else {
            $table .= '<tr><td style="height: 51px" id="' . ($hour - 1) . 'h' . ($row * 10) . 'm" ' . (($hour - 1 == 12 || $hour - 1 == 13) ? 'class="lanch"' : '') . '>' . ($row * 10) . 'хв.</td>';
        }
    }
    $row++;
    if($row == 6) {
        $row = 0;
    }
}
$table .= '</tbody>';
$table .= '</table>';
//echo '<pre>';
//var_dump(htmlspecialchars($table));
//echo '</pre>';
//die();
if(isset($_GET['id_usr'])&&!empty($_GET['id_usr'])){
    $sql = "select lastname, firstname from llx_user where rowid = ".$_GET['id_usr'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $username = '<b class="middle_size">співробитника '.$obj->lastname.' '.$obj->firstname.'</b>';
}

$backtopage = $_SERVER['REQUEST_URI'];
global $conf;
//var_dump($conf->browser->name);
//die();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/hourly_plan.html';
//print '</br>';
//print'<div style="float: left">test</div>';
//llxFooter();
llxPopupMenu();