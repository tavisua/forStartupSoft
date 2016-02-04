<?php
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/societe/modules_societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/societecontact_class.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/comm/action/class/actioncomm.class.php';

switch($_REQUEST['mainmenu']){
    case 'global_task':{
        $Action = 'GlobalAction';
        $Task   = 'GlobalTask';
        $actioncode = 'AC_GLOBAL';
    }break;
    case 'current_task':{
        $Action = 'CurrentAction';
        $Task   = 'CurrentTask';
        $actioncode = 'AC_CURRENT';
    }break;
}

$Action = $langs->trans($Action);
llxHeader("",$Action,"");
print_fiche_titre($Action);
$description = GetDescription($_GET['action_id']);
$actiontabe = ShowActionTable();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/'.$user->respon_alias.'/action/chain_action.html';
//include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/responsibility/sale/action/chain_action.html';
llxFooter();
exit();

function ShowActionTable(){
    global $db, $langs, $conf;
    $chain_actions = array();
    $chain_actions = GetChainActions($_GET['action_id']);
    $sql = 'select fk_parent, datep from `llx_actioncomm` where id in ('.implode(",", $chain_actions).') and fk_parent <> 0';
    $res = $db->query($sql);
    $nextaction = array();
    while($row = $db->fetch_object($res)){
        $nextaction[$row->fk_parent] = $row->datep;
    }

    $sql='select `llx_actioncomm`.id as rowid, `llx_societe_action`.dtChange as `datec`, `llx_user`.lastname,
        concat(case when `llx_societe_contact`.lastname is null then "" else `llx_societe_contact`.lastname end,
        case when `llx_societe_contact`.firstname is null then "" else `llx_societe_contact`.firstname end) as contactname,
        TypeCode.code kindaction, `llx_societe_action`.`said`, `llx_societe_action`.`answer`,`llx_societe_action`.`argument`,
        `llx_societe_action`.`said_important`, `llx_societe_action`.`result_of_action`, `llx_societe_action`.`work_before_the_next_action`
        from `llx_actioncomm`
        inner join (select code, libelle label from `llx_c_actioncomm` where active = 1 and (type = "system" or type = "user")) TypeCode on TypeCode.code = `llx_actioncomm`.code
        left join `llx_societe_contact` on `llx_societe_contact`.rowid=`llx_actioncomm`.fk_contact
        left join `llx_societe_action` on `llx_actioncomm`.id = `llx_societe_action`.`action_id`
        left join `llx_user` on `llx_societe_action`.id_usr = `llx_user`.rowid
        where id in ('.implode(",", $chain_actions).') order by datep desc';

//    die($sql);
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $out = '<tbody>';
//    var_dump($sql);
//    die();
    if($db->num_rows($res)==0){
        $out .= '<tr class="impair">
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 100px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 50px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 35px" class="middle_size">&nbsp;</td>
            </tr>';
    }
    $num=0;

    while($row = $db->fetch_object($res)){
        $dtChange = new DateTime($row->datec);

        if(isset($nextaction[$row->rowid])) {
            $row->date_next_action = $nextaction[$row->rowid];
//            var_dump($nextaction[$row->rowid]);
//            die();
        }
        $dtNextAction = new DateTime($row->date_next_action);
        $dtDateMentor = new DateTime($row->date_mentor);
        $iconitem='';
        $title='';
        switch($row->kindaction){
            case 'AC_GLOBAL':{
                $classitem = 'global_taskitem';
                $iconitem = 'object_global_task.png';
                $title=$langs->trans('ActionGlobalTask');
            }break;
            case 'AC_CURRENT':{
                $classitem = 'current_taskitem';
                $iconitem = 'object_current_task.png';
                $title=$langs->trans('ActionCurrentTask');
            }break;
            case 'AC_RDV':{
                $classitem = 'office_meetting_taskitem';
                $iconitem = 'object_office_meetting_task.png';
                $title=$langs->trans('ActionAC_RDV');
            }break;
            case 'AC_TEL':{
                $classitem = 'office_callphone_taskitem';
                $iconitem = 'object_call2.png';
                $title=$langs->trans('ActionAC_TEL');
            }break;
            case 'AC_DEP':{
                $classitem = 'departure_taskitem';
                $iconitem = 'object_departure_task.png';
                $title=$langs->trans('ActionDepartureMeeteng');
            }break;
        }
        $out .= '<tr class="'.(fmod($num++, 2)==0?'impair':'pair').'">
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'dtChange" style="widtd: 80px" class="middle_size">'.(empty($row->datec)?'':$dtChange->format('d.m.y H:i:s')).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'lastname" style="widtd: 100px" class="middle_size">'.$row->lastname.'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'contactname" style="widtd: 80px" class="middle_size">'.$row->contactname.'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'kindaction" style="widtd: 50px; text-align: center;" class="middle_size" ><img src="/dolibarr/htdocs/theme/'.$conf->theme.'/img/'.$iconitem.'" title="'.$title.'"></td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'said" style="widtd: 80px" class="middle_size">'.(strlen($row->said)>20?mb_substr($row->said, 0, 20, 'UTF-8').'...':$row->said).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'answer" style="widtd: 80px" class="middle_size">'.(strlen($row->answer)>20?mb_substr($row->answer, 0, 20, 'UTF-8').'...':$row->answer).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'argument" style="widtd: 80px" class="middle_size">'.(strlen($row->argument)>20?mb_substr($row->argument, 0, 20, 'UTF-8').'...':$row->argument).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'said_important" style="widtd: 80px" class="middle_size">'.(strlen($row->said_important)>20?mb_substr($row->said_important, 0, 20, 'UTF-8').'...':$row->said_important).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'result_of_action" style="widtd: 80px" class="middle_size">'.(strlen($row->result_of_action)>20?mb_substr($row->result_of_action, 0, 20, 'UTF-8').'...':$row->result_of_action).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'work_before_the_next_action" style="widtd: 80px" class="middle_size">'.(strlen($row->work_before_the_next_action)>20?mb_substr($row->work_before_the_next_action, 0, 20, 'UTF-8').'...':$row->work_before_the_next_action).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'date_next_action" style="widtd: 80px" class="middle_size">'.(empty($row->date_next_action)?'':$dtNextAction->format('d.m.y H:i:s')).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'work_before_the_next_action_mentor" style="widtd: 80px" class="middle_size">'.(strlen($row->work_mentor)>20?mb_substr($row->work_mentor, 0, 20, 'UTF-8').'...':$row->work_mentor).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'date_next_action_mentor" style="widtd: 80px" class="middle_size">'.(empty($row->date_mentor)?'':$dtDateMentor->format('d.m.y H:i:s')).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'action" style="width: 35px" class="middle_size"><script>
                 var click_event = "/dolibarr/htdocs/societe/addcontact.php?action=edit&mainmenu=companies&rowid=1";
                </script>';
            $out .= '<img id="img_1" "="" onclick="" style="vertical-align: middle" title="'.$langs->trans('AddSubAction').'" src="/dolibarr/htdocs/theme/eldy/img/Add.png">';
            $out .= '<img id="img_1" "="" onclick="EditAction('.$row->rowid.');" style="vertical-align: middle; cursor: pointer;" title="'.$langs->trans('Edit').'" src="/dolibarr/htdocs/theme/eldy/img/edit.png">';
            $out .= '</td>
            </tr>';
    }
//        <th style="width: 80px" class="middle_size">Дата і час внесення</th>
//            <th style="width: 100px" class="middle_size">Хто від нас вносив</th>
//            <th style="width: 80px" class="middle_size">З ким діяли</th>
//            <th style="width: 50px" class="middle_size">Вид дій</th>
//            <th style="width: 80px" class="middle_size">Що йому озвучили</th>
//            <th style="width: 80px" class="middle_size">Що він відповів</th>
//            <th style="width: 80px" class="middle_size">Чим аргументував</th>
//            <th style="width: 80px" class="middle_size">Що важливого сказав</th>
//            <th style="width: 80px" class="middle_size">Результат дій (резюме переговорника)</th>
//            <th style="width: 80px" class="middle_size">Робота до/на наступних дій</th>
//            <th style="width: 80px" class="middle_size">Дата наст.дій</th>
//            <th style="width: 80px" class="middle_size">Робота до/на наступних дій (завдання наставника)</th>
//            <th style="width: 80px" class="middle_size">Запропонована дата виконання наставником</th>
    $out .= '</tbody>';
    return $out;

}
function GetDescription($action_id){
    global $db;
    $sql = "select note from llx_actioncomm where id = ".$action_id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    return $obj->note;
}
function GetChainActions($action_id){
    global $db;
    $chain_actions = array();
    $chain_actions[]=$action_id;
    $Actions = new ActionComm($db);
    //Завантажую всі батьківські ІД
    while($action_id = $Actions->GetLastAction($action_id, 'id')){
        array_unshift($chain_actions, $action_id);
    }
    //Завантажую всі наступні ІД
    while($action_id = $Actions->GetNextAction($chain_actions[count($chain_actions)-1], 'id')){
        $chain_actions[] = $action_id;
    }
    return $chain_actions;
}

