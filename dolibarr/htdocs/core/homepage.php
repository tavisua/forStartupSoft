<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 27.11.2015
 * Time: 4:17
 */
//Шапка сторінки
//$table = '    <tbody>
//    <tr  class="pair">
//        <td>Кіровоградська</td>
//        <td>Онуфрієвський</td>
//        <td>120000</td>
//        <td>100000</td>
//        <td>45000</td>
//    </tr>
//
//    </tbody>';

$table = ShowTable();
include_once $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/homepage/index.html';

exit();

function ShowTable(){
    global $db, $user;
    $sql = 'select `regions`.`rowid`, `states`.`name` as states_name,`regions`.`name` as regions_name  from `llx_user_regions`
        left join `regions` on `regions`.`rowid` = `llx_user_regions`.`fk_id`
        inner join `states` on `states`.`rowid` = `regions`.`state_id`
        where `llx_user_regions`.`fk_user` = '.$user->id.'
        and `llx_user_regions`.`active` = 1
        order by states_name, regions_name';
    $regions = $db->query($sql);
    if(!$regions){
        var_dump($sql);
        dol_print_error($db);
    }
    $sql = 'select `regions_id`, `classifycation_id`,  `value` from `regions_param`
        where `regions_id` in
        (select fk_id from `llx_user_regions`
        where `llx_user_regions`.`fk_user` = '.$user->id.'
        and active = 1)';
    $res = $db->query($sql);
    if(!$res){
        var_dump($sql);
        dol_print_error($db);
    }
    $regions_param = array();
    while($row = $db->fetch_object($res)){
        $regions_param[$row->regions_id.'_'.$row->classifycation_id]=ceil($row->value);
    }
    $sql = 'select `llx_societe`.`region_id`, `llx_societe_classificator`.`classifycation_id`, SUM(`value`) as value from `llx_societe_classificator`
        inner join `llx_societe` on `llx_societe_classificator`.`soc_id` = `llx_societe`.rowid
        where `llx_societe`.`region_id` in
        (select fk_id from `llx_user_regions`
        where `llx_user_regions`.`fk_user` = '.$user->id.'
        and active = 1)
        group by `llx_societe`.`region_id`, `llx_societe_classificator`.`classifycation_id`';
    $res = $db->query($sql);
    if(!$res){
        var_dump($sql);
        dol_print_error($db);
    }
    $client_param = array();
    while($row = $db->fetch_object($res)){
        $client_param[$row->region_id.'_'.$row->classifycation_id]=ceil($row->value);
    }
    $out = '<tbody>';
    $pair = true;
    while($row = $db->fetch_object($regions)){
        $procent = 0;
        if(isset($client_param[$row->rowid.'_5'])&&isset($regions_param[$row->rowid.'_4'])){
            $procent = ceil($client_param[$row->rowid.'_5']/$regions_param[$row->rowid.'_4']*100);
        }
        if($procent<50){
            $color = 'rgb(255, 0, 0)';
        }elseif($procent>=50&&$procent<=75){
            $color = 'rgb(255, 153, 0)';
        }else
            $color = 'rgb(0, 255, 0)';
        $out.='<tr  class="'.($pair?('pair'):('impair')).'">
                    <td>'.trim($row->states_name).'</td>
                    <td>'.trim($row->regions_name).'</td>
                    <td class="middle_size">'.(isset($regions_param[$row->rowid.'_3'])?$regions_param[$row->rowid.'_3']:'').'</td>
                    <td class="middle_size">'.(isset($regions_param[$row->rowid.'_4'])?$regions_param[$row->rowid.'_4']:'').'</td>
                    <td class="middle_size">'.(isset($client_param[$row->rowid.'_5'])?$client_param[$row->rowid.'_5']:'').'</td>
                    <td class="middle_size" style="background:'.$color.' ">'.$procent.'%</td>
               </tr>';
        $pair = !$pair;
    }
    $out.='</tbody>';
    return $out;
}