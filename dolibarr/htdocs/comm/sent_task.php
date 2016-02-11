<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 08.02.2016
 * Time: 12:11
 */
$tasktable = ShowSentTask();
//var_dump(htmlspecialchars($senttask));
//die();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/comm/sent_task.html';

exit();

function ShowSentTask(){
    global $db, $user, $conf, $langs;
    $sql = "select `llx_actioncomm`.id, `llx_actioncomm`.`code`, `llx_user`.`lastname`, `llx_actioncomm`.`datec`, `datep2`, `percent`, `llx_actioncomm`.`label` title from `llx_actioncomm`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id`=`llx_actioncomm_resources`.`fk_actioncomm`
        left join llx_user on llx_user.rowid = `llx_actioncomm_resources`.`fk_element`
        where `llx_actioncomm`.`fk_user_author` = ".$user->id."
        and code in (select code from `llx_c_actioncomm` where type in ('user', 'system'))
        and `llx_actioncomm_resources`.`fk_element` <> `llx_actioncomm`.`fk_user_author`
        and `datea` is null
        order by datep2";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '';
    $nom = 0;
    while($obj = $db->fetch_object($res)){
        $class = (fmod($nom++, 2) == 0 ? "impair" : "pair");
        $datec = new DateTime($obj->datec);
        $datep2 = new DateTime($obj->datep2);
        //Статус завдання
        $date = new DateTime();
        $style = 'style="';
        if($datep2<$date){
            $style = 'style="background:rgb(255, 0, 0)';
        }elseif($datep2==$date){
            $style = 'style="background:rgb(0, 255, 0)';
        }
        $style .='"';
        $out .= '<tr id = "tr' . $obj->id . '" class="' . $class .($datep2<time()?(' out'):'').'" >';
        switch(trim($obj->code)){
            case 'AC_GLOBAL':{
                $classitem = 'global_taskitem';
                $iconitem = 'object_global_task.png';
            }break;
            case 'AC_CURRENT':{
                $classitem = 'current_taskitem';
                $iconitem = 'object_current_task.png';
            }break;
            case 'AC_RDV':{
                $classitem = 'office_meetting_taskitem';
                $iconitem = 'object_office_meetting_task.png';
            }break;
            case 'AC_TEL':{
                $classitem = 'office_callphone_taskitem';
                $iconitem = 'object_call.png';
            }break;
            case 'AC_DEP':{
                $classitem = 'departure_taskitem';
                $iconitem = 'object_departure_task.png';
            }break;
        }
        if($obj->percent == -1)
            $status = 'Не розпочато';
        elseif($obj->percent>0&&$obj->percent<100)
            $status = 'В роботі('.$obj->percent.'%)';
        else
            $status = 'Виконано';

        $out .= '<td class="small_size"><img src="http://'.$_SERVER['SERVER_NAME']. '/dolibarr/htdocs/theme/'.$conf->theme.'/img/'.$iconitem.'" title="'.$langs->trans($obj->title).'"></td>';
        $out.='<td class="small_size">'.$obj->lastname.'</td>';
        $out.='<td class="small_size">'.$datec->format('d.m').' '.$datec->format('H:i').'</td>';
        $out.='<td class="small_size">'.$datep2->format('d.m').' '.$datep2->format('H:i').'</td>';
        $out.='<td class="small_size" '.$style.'>'.$status.'</td>';
        $out.='</tr>';
    }
    return $out;
}