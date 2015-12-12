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
                    <th width="300px" colspan="8">Наявні завдання</th>
                </tr>
                <tr class="multiple_header_table">
                    <th class="small_size">Тип</th>
                    <th class="small_size">Початок</th>
                    <th class="small_size">Необх.</th>
                    <th class="small_size">Кінець</th>
                    <th class="small_size" width="150px">Направлення</th>
                    <th class="small_size" width="150px">Замовник</th>
                    <th class="small_size" width="200px">Задача</th>
                    <th class="small_size" width="170px">Статус</th>
                </tr>
           </thead>';
$row = 0;
$hour = 8;
$split=array();
//global $conf;
//var_dump($conf->browser->name);
$task_table = '<div class="task_cell" style="float: left; width: '.($conf->browser->name=='firefox'?'18px':'19px').'"><img src="theme/eldy/img/object_global_task.png" title="Глобальна задача"></div>
               <div class="task_cell" style="float: left; width: '.($conf->browser->name=='firefox'?'42px':'43px').'">12:00</div>
               <div class="task_cell" style="float: left; width: 36px">7</div>
               <div class="task_cell" style="float: left; width: 35px">12:07</div>
               <div class="task_cell" style="float: left; width: 152px">Кіровоградський район</div>
               <div class="task_cell" style="float: left; width: 152px">SuperAdmin</div>
               <div class="task_cell" style="float: left; width: 202px;">Станом на 1 число місяця проводити ревізію по наявності та списанню зч та матеріалів по кожному сервіснику та сервісній машині . Комісія в складі 3 чол..Протокол.Суровий офіціоз.</div>
               <div class="task_cell" style="float: left; width: 152px; border-color: transparent">в роботі</div>';
$task='<div id="11" class="global_taskitem" style="height: 216px" p>'.$task_table.'</div>';
$task.='<div id="currenttime"></div>';
//$task = 'test';
$table .= '<tbody id="schedule_body">';
for($period=0; $period<66;$period++){
    if($row == 0) {
        $table .= '<tr><td rowspan="6" style="vertical-align: text-top" width="32px" '.(($hour==12||$hour==13)?'class="lanch"':'').'>'.$hour++.'год <td id="'.($hour-1).'h0m" width="54px" '.(($hour-1==12||$hour-1==13)?'class="lanch"':'').'>00 хв.</td>'.($period==0&&$row==0?'<td rowspan="66" height="100%" width="auto" id="taskfield" >'.$task.'</td>':'');
    }else{
        $table .= '<tr><td id="'.($hour-1).'h'.($row*10).'m" '.(($hour-1==12||$hour-1==13)?'class="lanch"':'').'>'.($row*10).'хв.</td>';
    }
    $row++;
    if($row == 6) {
        $row = 0;
    }
}
$table .= '</tbody>';
$table .= '</table>';

if(!isset($_POST['date'])){
    $date = date('d.m.Y');
}else{
    $date = GETPOST('date', 'alpha');
}
$actionURL = '/comm/action/card.php';
$backtopage = $_SERVER['REQUEST_URI'];
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/hourly_plan.html';
//print '</br>';
//print'<div style="float: left">test</div>';
llxFooter();