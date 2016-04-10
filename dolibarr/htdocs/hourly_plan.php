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
//var_dump($_SERVER);
//echo '</pre>';
//die();

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
                    <th class="small_size" width="20px"></th>
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
$actionURL = '/comm/action/card.php';
$sql = "select `llx_actioncomm`.id as rowid, `llx_actioncomm`.datep, `llx_actioncomm`.datep2,
        `llx_actioncomm`.`code`, `llx_actioncomm`.label, `regions`.`name` as region_name, case when `llx_actioncomm`.fk_soc is null then `llx_user`.`lastname` else `llx_societe`.`nom` end lastname,
        `llx_actioncomm`.`note`, `llx_actioncomm`.`percent`, `llx_c_actioncomm`.`libelle` title, `llx_actioncomm`.confirmdoc,
        `llx_actioncomm`.priority
        from `llx_actioncomm`
        left join `llx_societe` on `llx_societe`.rowid = `llx_actioncomm`.fk_soc
        left join `states` on `states`.rowid = `llx_societe`.state_id
        left join `regions` on `regions`.rowid=`llx_societe`.region_id
        left join `llx_user` on `llx_user`.rowid= `llx_actioncomm`.fk_user_author
        left join `llx_c_actioncomm` on `llx_c_actioncomm`.`code` = `llx_actioncomm`.`code`
        left join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where fk_action in
              (select id from `llx_c_actioncomm`
              where `type` in ('system', 'user'))
        and (`llx_actioncomm_resources`.`fk_element`= ".$user->id." or (`llx_actioncomm`.`fk_user_author`= ".$user->id." and `llx_actioncomm`.id not in (select `llx_actioncomm_resources`.`fk_actioncomm` from `llx_actioncomm_resources` where `llx_actioncomm_resources`.`fk_element`= ".$user->id.")))
        and `datep` between '".$dateQuery->format('Y-m-d')."' and date_add('".$dateQuery->format('Y-m-d')."', interval 1 day)
        and `llx_actioncomm`.active = 1
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
    if ($row->percent == -1)
        $status = 'Не розпочато';
    elseif ($row->percent > 0 && $row->percent < 100)
        $status = 'В роботі(' . $row->percent . '%)';
    else
        $status = 'Виконано';
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
    $task_table = '<div class="task_cell" style="float: left; width: ' . ($conf->browser->name == 'firefox' ? '23px' : '24px') . '"><img src="theme/' . $conf->theme . '/img/' . $iconitem . '" title="' . $langs->trans($row->title) . '"></div>
           <div class="task_cell" style="float: left; width: ' . ($conf->browser->name == 'firefox' ? '42px' : '43px') . '">' . $datep->format('H:i') . '</div>
           <div class="task_cell" style="float: left; width: 36px; height 16px">' . $DiffTime . '</div>
           <div class="task_cell" style="float: left; width: 35px">' . $datep2->format('H:i') . '</div>
           <div class="task_cell" style="float: left; width: 152px">' . trim($row->region_name) . ' район</div>
           <div class="task_cell" style="float: left; width: 152px">' . trim($row->lastname) . '</div>
           <div class="task_cell" style="float: left; width: 202px;">' . trim($row->note) . '</div>
           <div class="task_cell" style="float: left; width: 152px;">' . trim($row->confirmdoc) . '</div>
           <div class="task_cell" style="float: left; width: 130px;">' . $status . '</div>
           <div class="task_cell" style="float: left; width: 15px; border-color: transparent"><img src="theme/eldy/img/edit.png"></div>';
//    $task .= '<div id="'.$row->rowid.'" class="'.$classitem.'" style="height: 216px" >' . $task_table . '</div>';

    $task .= '<tr id="' . $row->rowid . '"><td class="' . $classitem . '" >' . $task_table . '</td></tr>';
//    $task.='<tr id="'.$row->rowid.'"><td class="'.$classitem.'" style="height: '.($DiffSec/600*($conf->browser->name == 'firefox' ? ($DiffSec/60<=30?($DiffSec/60<15?22:23.8):23.7) : 22)).'px">'.$task_table.'</td></tr>';
    $prev_time = mktime($datep2->format('H'), $datep2->format('i'), $datep2->format('s'), $datep2->format('m'), $datep2->format('d'), $datep2->format('Y'));
}
if($db->num_rows($res))
    $task.='    </tbody>
                </table></div>';

//$task = 'test';
$table .= '<tbody id="schedule_body">';
$totalcount = 144;
for($period=0; $period<$totalcount;$period++){
    if($db->num_rows($res)) {
        if ($row == 0) {
            $table .= '<tr><td rowspan="6" style="vertical-align: text-top" width="32px" ' . (($hour == 12 || $hour == 13) ? 'class="lanch"' : '') . '>' . $hour++ . 'год <td id="' . ($hour - 1) . 'h0m" width="54px" ' . (($hour - 1 == 12 || $hour - 1 == 13) ? 'class="lanch"' : '') . '>'.'00 хв.</td>' . ($period == 0 && $row == 0 ? '<td rowspan="'.$totalcount.'" height="100%" id="taskfield" valign="top" >' . $task . '</td>' : '');
        } else {
            $table .= '<tr><td id="' . ($hour - 1) . 'h' . ($row * 10) . 'm" ' . (($hour - 1 == 12 || $hour - 1 == 13) ? 'class="lanch"' : '') . '>' . ($row * 10) . 'хв.</td>';
        }
    }else{
        if ($row == 0) {
            $table .= '<tr><td rowspan="6" style="vertical-align: text-top" width="32px" ' . (($hour == 12 || $hour == 13) ? 'class="lanch"' : '') . '>' . $hour++ . 'год <td id="' . ($hour - 1) . 'h0m" width="54px" ' . (($hour - 1 == 12 || $hour - 1 == 13) ? 'class="lanch"' : '') . '>00 хв.</td>' . ($period == 0 && $row == 0 ? '<td rowspan="'.$totalcount.'" height="100%" width="100%" id="taskfield" valign="top" ></td>' : '');
        } else {
            $table .= '<tr><td id="' . ($hour - 1) . 'h' . ($row * 10) . 'm" ' . (($hour - 1 == 12 || $hour - 1 == 13) ? 'class="lanch"' : '') . '>' . ($row * 10) . 'хв.</td>';
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

$backtopage = $_SERVER['REQUEST_URI'];
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/hourly_plan.html';
//print '</br>';
//print'<div style="float: left">test</div>';
//llxFooter();