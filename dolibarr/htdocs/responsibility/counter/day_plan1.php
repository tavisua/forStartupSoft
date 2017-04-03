<?php

require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';


$actions = array();
$future = array();
$outstanding = array();
$CustActions = array();
$userActions = array();
$actioncode = array('AC_GLOBAL', 'AC_CURRENT');

if(isset($_GET['id_usr'])&&!empty($_GET['id_usr'])){
    global $db;
    $sql = 'select lastname from llx_user where rowid = '.$_GET['id_usr'];
    $res = $db->query($sql);
    $obj = $db->fetch_object($res);
    $username = $obj->lastname;
    $id_usr = $_GET['id_usr'];
}else {
    $id_usr = $user->id;
    $username = $user->lastname;
}
$sql = 'select name from subdivision where rowid = '.(empty($user->subdiv_id)?0:$user->subdiv_id);
$res = $db->query($sql);
if(!$res)
    dol_print_error($db);
$obj = $db->fetch_object($res);
$subdivision = $obj->name;
//if(!isset($_SESSION['actions'])) {

//}else {
//    $actions = $_SESSION['actions'];
//}

//echo '<pre>';
//var_dump($actions);
//echo '</pre>';
//die();

llxHeader("",$langs->trans('PlanOfDays'),"");
print_fiche_titre($langs->trans('PlanOfDays'));



$table = ShowTable();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/day_plan.html';
llxPopupMenu();
//print '</br>';
//print'<div style="float: left">test</div>';
//llxFooter();

exit();

function ShowTable(){
    global $actions,$user,$CustActions,$userActions,$actioncode;
    $today = new DateTime();
    $mkToday = dol_mktime(0,0,0,$today->format('m'),$today->format('d'),$today->format('Y'));

    foreach($actions as $action){
//        echo '<pre>';
//        var_dump($action);
//        echo '</pre>';
//        die();

        $obj = (object)$action;
        $date = new DateTime($obj->datep);
        $mkDate = dol_mktime(0,0,0,$date->format('m'),$date->format('d'),$date->format('Y'));
        if($action['id_usr']==$user->id){

            $userActions[$obj->datep][$obj->code]++;

            if($mkDate >= $mkToday) { //Future actions
                if($mkDate-$mkToday<=604800) {//604800 sec by week
                    $userActions['future_week'][$obj->code]++;
                }if($mkDate-$mkToday<=2678400)//2678400 sec by month
                    $userActions['future_month'][$obj->code]++;
            }
            if($mkDate <= $mkToday && !in_array($obj->percent, array(100, -100))) {
                $userActions['outstanding'][$obj->code]++;
            }
            if($mkDate <= $mkToday && $obj->percent == 100 && (in_array($action['code'], $actioncode) || $action['callstatus'] == '5')){
//                if($obj->datep == '2016-05-30'){
//                        echo '<pre>';
//                        var_dump($action['rowid'], $action['code'], $action['callstatus']);
//                        echo '</pre>';
//                }
                $userActions['fact_'.$obj->datep][$obj->code]++;
                if($mkToday-$mkDate<=604800)//604800 sec by week
                    $userActions['fact_week'][$obj->code]++;
                if($mkToday-$mkDate<=2678400)//2678400 sec by month
                    $userActions['fact_month'][$obj->code]++;
            }
            if($mkDate <= $mkToday){
                $userActions['total_'.$obj->datep][$obj->code]++;
                if($mkToday-$mkDate<=604800)//604800 sec by week
                    $userActions['total_week'][$obj->code]++;
                if($mkToday-$mkDate<=2678400)//2678400 sec by month
                    $userActions['total_month'][$obj->code]++;                
            }
        }
        if($mkDate <= $mkToday && $action['percent'] == 100 && $action['code'] == 'AC_CUST' && $action['callstatus'] == '5'){
                if($mkToday-$mkDate<=604800&&$mkToday-$mkDate>=0)//604800 sec by week
                    $CustActions[$action['id_usr']]++;

        }
    }
//    echo '<pre>';
//    var_dump($userActions);
//    echo '</pre>';
//    die();
    $table = '<tbody id="reference_body">';
//Всього задач
    $table.='<tr><td class="middle_size" style="width: 105px"><b>Всього задач</b></td>
    <td style="width: 175px">&nbsp;</td>';
    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        if($i < 8) {
            $percent = '';
            if($i<7) {
                $count = (isset($userActions['fact_' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? array_sum(($userActions['fact_' . date("Y-m-d", (time() - 3600 * 24 * $i))])) : ('0'));
                $total = (isset($userActions['total_' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? array_sum(($userActions['total_' . date("Y-m-d", (time() - 3600 * 24 * $i))])) : (''));
            }else{
                $count = isset($userActions['fact_week'])?array_sum($userActions['fact_week']):'';
                $total = isset($userActions['total_week'])?array_sum($userActions['total_week']):'';
            }
        }else{
            $count = isset($userActions['fact_month'])?array_sum(($userActions['fact_month'])):('0');
            $total = isset($userActions['total_month'])?array_sum(($userActions['total_month'])):('0');
        }
        if(!empty($total))
            $percent = round(100*$count/($total==0?1:$total));
        $table .= '<td class="middle_size" style="width: ' . (in_array($i, array(0,8))?'35':'30') . 'px; text-align:center;">' . $percent. '</td>';
    }
    //минуле факт
    for($i=8; $i>=0; $i--){
        if($i < 8)
            $table.='<td class="middle_size" style="width: '.(in_array($i, array(0,8))?'35':'30').'px; text-align:center;">'.($i<7?(isset($userActions['fact_'.date("Y-m-d", (time()-3600*24*$i))])?array_sum(($userActions['fact_'.date("Y-m-d", (time()-3600*24*$i))])):('')):(isset($userActions['fact_week'])?array_sum($userActions['fact_week']):'')).'</td>';
        else
            $table.='<td class="middle_size" style="width: '.(in_array($i, array(0,8))?'35':'30').'px; text-align:center;">'.(isset($userActions['fact_month'])?array_sum($userActions['fact_month']):('0')).'</td>';
    }
    //прострочено
    $value = '';
    if(!empty($userActions['outstanding']))
        $value = array_sum($userActions['outstanding']);
    $table .= '<td class="middle_size" style="text-align: center; width: 51px">' . $value . '</td>';
    //майбутнє заплановано
    for($i=0; $i<9; $i++){
        $value = '';
        if($i < 8) {
            if($i < 7)
                $value =  (isset($userActions[date("Y-m-d", (time() + 3600 * 24 * $i))]) ? array_sum($userActions[date("Y-m-d", (time() + 3600 * 24 * $i))]) : (''));
            else {
                if(!empty($userActions['future_week']))
                    $value = (array_sum($userActions['future_week']));
            }
            $table .= '<td class="middle_size" style="text-align: center; width: ' . (in_array($i, array(0))?'35':'30') . 'px">'.(($i < 7)?'<a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date=' . date("Y-m-d", (time() + 3600 * 24 * $i)) . '">':'') . $value . (($i < 7)?'</a>':'').'</td>';
        }else {
            if(!empty($userActions['future_month']))
                $value = array_sum($userActions['future_month']);
            $table .= '<td class="middle_size" style="text-align: center; width: 35px">' . $value . '</td>';
        }
    }
    $table.='</tr>';
//Всього глобальні задачі
    $table.= ShowTasks('AC_GLOBAL', 'Глобальні задачі(ТОПЗ)');
    $table.= ShowTasks('AC_CURRENT', 'Поточні задачі');
    $table.= ShowTasks('AC_CUST', 'Всього по напрямках');

    $userActions = array();
    require_once DOL_DOCUMENT_ROOT.'/core/lib/day_plan.php';
    $bestuser_id = GetBestUserID();
//    var_dump($bestuser_id);
//    die();
    foreach($actions as $action){
        if($action['id_usr']==$bestuser_id){
            $obj = (object)$action;
            $userActions[$obj->datep][$obj->code]++;
            $date = new DateTime($obj->datep);
            $mkDate = dol_mktime(0,0,0,$date->format('m'),$date->format('d'),$date->format('Y'));
            if($mkDate >= $mkToday) { //Future actions
                if($mkDate-$mkToday<=604800) {//604800 sec by week
                    $userActions['future_week'][$obj->code]++;
                }if($mkDate-$mkToday<=2678400)//2678400 sec by month
                    $userActions['future_month'][$obj->code]++;
            }
            if($mkDate <= $mkToday && !in_array($obj->percent, array(100, -100))) {
                $userActions['outstanding'][$obj->code]++;
            }
            if($mkDate <= $mkToday && $obj->percent == 100 && (in_array($obj->code, $actioncode) || $obj->callstatus == '5') ){
                $userActions['fact_'.$obj->datep][$obj->code]++;
                if($mkToday-$mkDate<=604800)//604800 sec by week
                    $userActions['fact_week'][$obj->code]++;
                if($mkToday-$mkDate<=2678400)//2678400 sec by month
                    $userActions['fact_month'][$obj->code]++;
            }
            if($mkDate <= $mkToday){
                $userActions['total_'.$obj->datep][$obj->code]++;
                if($mkToday-$mkDate<=604800)//604800 sec by week
                    $userActions['total_week'][$obj->code]++;
                if($mkToday-$mkDate<=2678400)//2678400 sec by month
                    $userActions['total_month'][$obj->code]++;
            }
        }
    }
//        echo '<pre>';
//        var_dump($userActions);
//        echo '</pre>';
//        die();
    $table.= ShowTasks('AC_CUST', 'Найкращі показники по підрозділу', true);
    global $id_usr;
    $table.= getLineActiveList($id_usr);


    $table .= '</tbody>';
    return $table;
}
function ShowTasks1($Code, $Title, $bestvalue = false){
    global $userActions;
//    if($Title == 'Найкращі показники по підрозділу') {
//        echo '<pre>';
//        var_dump($userActions);
//        echo '</pre>';
//        die();
//    }
    $table='<tr '.($bestvalue?'class="bestvalue"':'').'><td colspan="2" class="middle_size" style="width: 105px;"><b>'.$Title.'</b></td>';
    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        $percent = '';
        if($i < 8) {
            if($i<7) {
                $count = (isset($userActions['fact_' . date("Y-m-d", (time() - 3600 * 24 * $i))][$Code]) ? $userActions['fact_' . date("Y-m-d", (time() - 3600 * 24 * $i))][$Code] : ('0'));
                $total = (isset($userActions['total_' . date("Y-m-d", (time() - 3600 * 24 * $i))][$Code]) ? $userActions['total_' . date("Y-m-d", (time() - 3600 * 24 * $i))][$Code] : (''));
            }else{
                $count = $userActions['fact_week'][$Code];
                $total = $userActions['total_week'][$Code];
            }
        }else{
            $count = isset($userActions['fact_month'][$Code])?$userActions['fact_month'][$Code]:('0');
            $total = isset($userActions['total_month'][$Code])?$userActions['total_month'][$Code]:('0');
        }
        if(!empty($total))
            $percent = round(100*$count/($total==0?1:$total));
        $table .= '<td class="middle_size" style="width: ' . (in_array($i, array(0,8))?'35':'30') . 'px; text-align:center;">' . $percent. '</td>';
    }
    //минуле факт
    for($i=8; $i>=0; $i--){
        if($i < 8)
            $table.='<td class="middle_size" style="width: '.(in_array($i, array(0,8))?'35':'30').'px; text-align:center;">'.($i<7?(isset($userActions['fact_'.date("Y-m-d", (time()-3600*24*$i))][$Code])?$userActions['fact_'.date("Y-m-d", (time()-3600*24*$i))][$Code]:('')):$userActions['fact_week'][$Code]).'</td>';
        else
            $table.='<td class="middle_size" style="width: '.(in_array($i, array(0,8))?'35':'30').'px; text-align:center;">'.(isset($userActions['fact_month'][$Code])?$userActions['fact_month'][$Code]:('')).'</td>';
    }
    //прострочено
    $value = '';
    if(!empty($userActions['outstanding'][$Code]))
        $value = $userActions['outstanding'][$Code];
    $table .= '<td class="middle_size" style="text-align: center; width: 51px">' . $value . '</td>';
    //майбутнє заплановано
    for($i=0; $i<9; $i++){
        $value = '';
        if($i < 8) {
            if($i < 7)
                $value =  (isset($userActions[date("Y-m-d", (time() + 3600 * 24 * $i))][$Code]) ? $userActions[date("Y-m-d", (time() + 3600 * 24 * $i))][$Code] : (''));
            else {
                if(!empty($userActions['future_week'][$Code]))
                    $value = $userActions['future_week'][$Code];
            }
            $table .= '<td class="middle_size" style="text-align: center; width: ' . (in_array($i, array(0))?'35':'30') . 'px">'.(($i < 7)?'<a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date=' . date("Y-m-d", (time() + 3600 * 24 * $i)) . '">':'') . $value . (($i < 7)?'</a>':'').'</td>';
        }else {
            if(!empty($userActions['future_month'][$Code]))
                $value = $userActions['future_month'][$Code];
            $table .= '<td class="middle_size" style="text-align: center; width: 35px">' . $value . '</td>';
        }
    }
    $table.='</tr>';
    return $table;
}