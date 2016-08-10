<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 03.08.2016
 * Time: 7:44
 */
function getSubLineActive($lineactive=array()){
    global $db;
    if(count($lineactive)==0)
        $lineactive[]=-1;
    $sql = "select path_id, category_id from `oc_category_path` where path_id in (".implode(',',$lineactive).")";
//    var_dump($sql);
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $lineactiveID = array();
    while($obj = $db->fetch_object($res)){
        if(!in_array($obj->category_id, $lineactiveID))
            $lineactiveID[$obj->path_id][] = $obj->category_id;
    }
    return $lineactiveID;
//    echo '<pre>';
//    var_dump(implode(',',$lineactive));
//    echo '</pre>';
//    die();
}
function loadActions($id_usr){
    global $db,$user;
    $sql = 'select subdivision.rowid, subdivision.name from subdivision, llx_user where llx_user.rowid = '.$id_usr.' and llx_user.subdiv_id = subdivision.rowid';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $subdiv_id = $obj->rowid;
    $subdivision = $obj->name;
    //if(!isset($_SESSION['actions'])) {
        $sql = "select distinct sub_user.rowid  id_usr, sub_user.alias, `llx_societe`.`categoryofcustomer_id`, `llx_societe`.`rowid` socid, llx_actioncomm.id, llx_actioncomm.percent, date(llx_actioncomm.datep) datep, llx_actioncomm.percent,
        case when llx_actioncomm.`code` in ('AC_GLOBAL', 'AC_CURRENT') then llx_actioncomm.`code` else 'AC_CUST' end `code`, `llx_societe_action`.`callstatus`
        from llx_actioncomm
        inner join (select id from `llx_c_actioncomm` where type in('user','system') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = llx_actioncomm.id
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        left join `llx_societe_action` on `llx_societe_action`.`action_id` = `llx_actioncomm`.`id`
        inner join (select `llx_user`.rowid, `responsibility`.`alias` from `llx_user` inner join `responsibility` on `responsibility`.`rowid` = `llx_user`.`respon_id` where `llx_user`.`subdiv_id` = ".$subdiv_id." and `llx_user`.`active` = 1) sub_user on sub_user.rowid = case when llx_actioncomm_resources.fk_element is null then llx_actioncomm.`fk_user_author` else llx_actioncomm_resources.fk_element end
        where 1
        and llx_actioncomm.active = 1
        and datep2 between adddate(date(now()), interval -1 month) and adddate(date(now()), interval 1 month)";
    //echo '<pre>';
    //var_dump($sql);
    //echo '</pre>';
    //die();
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
    //Формую список контрагентів, з якими були пов'язані завдання
        $socidArray = array();
        while ($obj = $db->fetch_object($res)) {
            if(!empty($obj->socid)&&!in_array($obj->socid, $socidArray)){
                $socidArray[]=$obj->socid;
            }
        }
    if(count($socidArray) == 0)
        $socidArray[]=-1;
    //Визначаю сферу відповідальності користувача
    $sql = "select res1.alias, res2.alias alias2 from llx_user
        left join responsibility res1 on res1.rowid = llx_user.respon_id
        left join responsibility res2 on res1.rowid = llx_user.respon_id2
        where llx_user.rowid = ".$id_usr;
    $resAlias = $db->query($sql);
    $objAlias = $db->fetch_object($resAlias);
    $arrayAlias = array($objAlias->alias, $objAlias->alias2);
//    var_dump(in_array($arrayAlias, array('purchase', 'service')));
//    die();
    $actions = array();

    if(count(array_intersect($arrayAlias, array('jurist', 'paperwork','corp_manager','counter','logistika')))) {//Якщо користувач - юрист, логіст, корпоративне управління, діловодство, бухгалтерія
        mysqli_data_seek($res,0);
        $time = time();
        while ($obj = $db->fetch_object($res)) {
            $actions[] = array('id_usr' => $obj->id_usr, 'rowid'=>$obj->id, 'socid'=>$obj->socid, 'region_id' => $obj->categoryofcustomer_id, 'respon_alias' => $obj->alias, 'percent' => $obj->percent, 'datep' => $obj->datep, 'code' => $obj->code, 'callstatus'=>$obj->callstatus);
        }
    }
    if(count(array_intersect($arrayAlias, array('purchase', 'service')))) {//Якщо користувач - постачальник-сервісник (для сервіса зробити додатково райони по клієнтам)
        //Визначаю напрямки діяльності вибраних контрагентів
        $sql = "select fk_soc, fk_lineactive from llx_societe_lineactive
            where fk_soc in (" . implode(',', $socidArray) . ")
            and active = 1
            order by fk_soc";
        $resLineactive = $db->query($sql);
        $fksoc = 0;
        $num = 0;
        $outLineactive = array();
        $lineactive = array();
        while ($obj = $db->fetch_object($resLineactive)) {
            $num++;
            if ($fksoc != $obj->fk_soc || $num == $db->num_rows($resLineactive)) {
                if ($fksoc != 0 || $num == $db->num_rows($resLineactive)) {
                    //                if($fksoc == 9963){
                    //                    echo '<pre>';
                    //                    var_dump(getSubLineActive($lineactive));
                    //                    echo '</pre>';
                    //                    die('test');
                    //                }
                    $outLineactive[$fksoc] = getSubLineActive($lineactive);
                }
                $lineactive = array();
                $fksoc = $obj->fk_soc;
            }
            if (!in_array($obj->fk_lineactive, $lineactive))
                $lineactive[] = $obj->fk_lineactive;
        }
//    echo '<pre>';
//    var_dump($lineactive);
//    echo '</pre>';
//    die();
        if (count($lineactive) == 0)
            $lineactive[] = -1;
        //Визначаю напрямки діяльності постачальника
        $tmp_lineactive = array();
        $sql = "select distinct fk_lineactive from llx_user_lineactive
          where fk_user = " . $id_usr . " and active = 1";
        $resUserLineActive = $db->query($sql);
        $outUserLineActive = array();
        while ($obj = $db->fetch_object($resUserLineActive)) {
            if (!in_array($obj->fk_lineactive, $lineactive))
                $tmp_lineactive[] = $obj->fk_lineactive;
        }
        $outUserLineActive = getSubLineActive($tmp_lineactive);
//    echo '<pre>';
//    var_dump($outUserLineActive);
//    var_dump($outLineactive);
//    echo '</pre>';
//    die();
        mysqli_data_seek($res,0);
        $time = time();
        while ($obj = $db->fetch_object($res)) {
            //Визначаю напрямок діяльності поточного контрагента
            if(!empty($obj->socid)) {
                if(count($outLineactive[$obj->socid]) == 0)
                    $lineactive_id = 0;
                else {
                    foreach($outLineactive[$obj->socid] as $item){
                        foreach($outUserLineActive as $key=>$array){
                            $resArray = array_intersect($item,$array);
                            if(count($resArray)>0) {
                                $lineactive_id = $key;
                                break;
                            }
    //                        echo '<pre>';
    //                        var_dump($key);
    //                        echo '</pre>';
    //                        die('test');
                        }
                    }
                }
            }
            $actions[] = array('id_usr' => $obj->id_usr, 'rowid'=>$obj->id, 'socid'=>$obj->socid, 'region_id' => $lineactive_id, 'respon_alias' => $obj->alias, 'percent' => $obj->percent, 'datep' => $obj->datep, 'code' => $obj->code, 'callstatus'=>$obj->callstatus);
        }
    }

//    echo '<pre>';
//    var_dump($actions);
//    echo '</pre>';
//    die();
//        $_SESSION['actions'] = $actions;
    return $actions;
}
function getLineActiveList($id_usr){
    global $db, $actions,$actioncode,$user;
    $actioncode = array('AC_GLOBAL', 'AC_CURRENT');
    if($actions == null)
        $actions = loadActions($id_usr);
//    $sql = "select subdiv_id from llx_user where rowid = ".$id_usr;
//    $res = $db->fetch_object($sql);
//    if(!$res)
//        dol_print_error($res);
//    $obj = $db->fetch_object($res);
//    $subdiv_id = $obj->subdiv_id;

//    echo '<pre>';
//    var_dump($actions);
//    echo '</pre>';
//    die();
    $outstanding = array();
    $future=array();
    $fact = array();
    $total = array();
    $regions = array();
    $maxAction = array();
    $today = new DateTime();
    $mkToday = dol_mktime(0,0,0,$today->format('m'),$today->format('d'),$today->format('Y'));
    $count = 0;
    $sql = "select responsibility.alias, resp2.alias as alias2 from llx_user
        left join responsibility on responsibility.rowid = llx_user.respon_id
        left join responsibility resp2 on resp2.rowid = llx_user.respon_id2
        where llx_user.rowid = ".$id_usr;

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);

    $respon_alias = array();
    if($db->num_rows($res)>0){
        $obj = $db->fetch_object($res);
        if(!empty($obj->alias))
            $respon_alias[]=$obj->alias;
        if(!empty($obj->alias2))
            $respon_alias[]=$obj->alias2;
    }
//    echo '<pre>';
//    var_dump($respon_alias);
//    echo '</pre>';
//    die();
    foreach($actions as $item){

        if($item["id_usr"]==$id_usr&&$item["code"]=='AC_CUST'&&in_array($item["respon_alias"],$respon_alias)){
//            if($item["socid"]==9963) {
//                var_dump($item);
//                die();
//            }
            if(!in_array(empty($item["region_id"])?'null':$item["region_id"], $regions))
                $regions[]=empty($item["region_id"])?'null':$item["region_id"];
            $date = new DateTime($item["datep"]);
            $mkDate = dol_mktime(0, 0, 0, $date->format('m'), $date->format('d'), $date->format('Y'));
//            echo $item["datep"].' '.var_dump($mkDate >= $mkToday).'</br>';

//            if($item["datep"]=='2016-05-11'){
//                die('test');
//            }
            if ($mkDate == $mkToday)
                $count++;
            if ($mkDate >= $mkToday) {
                $future[$item["region_id"]][$item["datep"]]++;
                if ($mkDate - $mkToday <= 604800)//604800 sec by week
                    $future[$item["region_id"]]['week']++;
                if ($mkDate - $mkToday <= 2678400)//2678400 sec by month
                    $future[$item["region_id"]]['month']++;
            }

            if($mkDate < $mkToday && $item['percent'] != 100){//Додав $mkDate < $mkToday. Вважається логічним, щоб кількість прострочених рахувати, коли завдання повинно вже було бути виконано
                $outstanding[$item["region_id"]]++;
            }
            if($item['percent'] == 100 && (in_array($item['code'], $actioncode)||$item['callstatus']=='5')){
                $fact[$item["region_id"]][$item["datep"]]++;
                if($mkToday-$mkDate<=604800)//604800 sec by week
                    $fact[$item["region_id"]]['week']++;
                if($mkToday-$mkDate<=2678400)//2678400 sec by month
                    $fact[$item["region_id"]]['month']++;
            }

            $total[$item["region_id"]][$item["datep"]]++;
            if($mkToday-$mkDate<=604800)//604800 sec by week
                $total[$item["region_id"]]['week']++;
            if($mkToday-$mkDate<=2678400)//2678400 sec by month
                $total[$item["region_id"]]['month']++;
        }
    }
//    echo '<pre>';
//    var_dump($fact);
//    echo '</pre>';
//    die();
    $lineactive = getLineActive($id_usr);
    $lineactive[0]=array("name"=> "Напрямок не вказано", "type"=>"");

//    echo '<pre>';
//    var_dump($lineactive);
//    echo '</pre>';
//    die();
//    $sql = "select `regions`.`rowid`,`states`.`name` statename, `regions`.`name` from `regions`
//        inner join `states` on `states`.`rowid` = `regions`.`state_id`
//        where (`regions`.`rowid` in (select `llx_user_regions`.fk_id from `llx_user_regions` where `llx_user_regions`.fk_user = ".$id_usr."
//              and `llx_user_regions`.active = 1)
//              or `regions`.`rowid` in (".(count($regions)>0?implode(",",$regions):0)."))
//        and `regions`.active = 1";
//    if(count($regions)>0 && in_array('null', $regions))
//        $sql.=" union select null, 'Район', 'не вказано'";
//    $sql.=" order by statename, `name`";

    $out = '';
//    $res = $db->query($sql);
//    if(!$res)
//        dol_print_error($db);
//    while($obj = $db->fetch_object($res)){
    $userFilter = '';
    if($id_usr != $user->id)
        $userFilter='&id_usr='.$id_usr;

    foreach(array_keys($lineactive) as $key){
        $out.='<tr id="reg'.$key.'" class="regions subtype middle_size '.$_REQUEST['class'].'">';
        if(!empty($key)) {
            $out .= '<td colspan="2"><a href="/dolibarr/htdocs/responsibility/purchase/area.php?idmenu=10425&filter=&mainmenu=area&leftmenu='.$userFilter.'&lineactive=' . $key . '" target="_blank">' . $lineactive[$key]['name'] . '</a></td>';
            if($_SERVER["PHP_SELF"] == "/dolibarr/htdocs/responsibility/gen_dir/day_plan.php")
                $out .= '<td></td>';
        }else{
            $out .= '<td colspan="2">'. $lineactive[$key]['name'] . '</td>';
            if($_SERVER["PHP_SELF"] == "/dolibarr/htdocs/responsibility/gen_dir/day_plan.php")
                $out .= '<td></td>';
        }
//        $out.='<td></td>';
        //відсоток виконання
        if(isset($total[$key]['month'])){
            $value = round($fact[$key]['month']/$total[$key]['month']*100,0);
            $out.='<td style="text-align: center">'.$value.'</td>';
        }else
            $out.='<td></td>';
        if(isset($total[$key]['week'])){
            $value = round($fact[$key]['week']/$total[$key]['week']*100,0);
            $out.='<td style="text-align: center">'.$value.'</td>';
        }else
            $out.='<td></td>';
        for($i=6;$i>=0;$i--) {
            if(isset($total[$key][date("Y-m-d", (time()-3600*24*$i))])){
                $value = round($fact[$key][date("Y-m-d", (time()-3600*24*$i))]/$total[$key][date("Y-m-d", (time()-3600*24*$i))]*100,0);
                $out.='<td style="text-align: center">'.$value.'</td>';
            }else
                $out .= '<td></td>';
        }
        //фактично виконано
//        echo '<pre>';
//        var_dump($key, $fact[$key]);
//        echo '</pre>';
//        die();
        if(isset($fact[$key]['month']))
                $out.='<td style="text-align: center">'.$fact[$key]['month'].'</td>';
            else
                $out.='<td></td>';
        if(isset($fact[$key]['week']))
                $out.='<td style="text-align: center">'.$fact[$key]['week'].'</td>';
            else
                $out.='<td></td>';
        for($i=6;$i>=0;$i--){
            if(isset($fact[$key][date("Y-m-d", (time()-3600*24*$i))]))
                $out.='<td style="text-align: center">'.$fact[$key][date("Y-m-d", (time()-3600*24*$i))].'</td>';
            else
                $out.='<td style="text-align: center"></td>';
        }
        //прострочено
        if(isset($outstanding[$key])) {
            $out .= '<td id="outstanding'.$key.'" style="text-align: center; cursor: pointer;" onclick="ShowOutStandingRegion('.$key.', '.$id_usr.');">' . $outstanding[$key] . '</td>';
        }else
            $out.='<td></td>';
        //заплановано на майбутнє
        for($i=0;$i<=6;$i++){
            if($future[$key][date("Y-m-d", (time()+3600*24*$i))])
                $out.='<td style="text-align: center">'.$future[$key][date("Y-m-d", (time()+3600*24*$i))].'</td>';
            else
                $out.='<td></td>';
        }
        if(isset($future[$key]['week']))
                $out.='<td style="text-align: center">'.$future[$key]['week'].'</td>';
            else
                $out.='<td></td>';
        if(isset($future[$key]['month']))
                $out.='<td style="text-align: center">'.$future[$key]['month'].'</td>';
            else
                $out.='<td></td>';
        $out.='</tr>';
    }
    return $out;
}
function getLineActiveService($id_usr){
    global $db, $actions,$actioncode,$user;
    $actioncode = array('AC_GLOBAL', 'AC_CURRENT');
    if($actions == null)
        $actions = loadActions($id_usr);
//    echo '<pre>';
//    var_dump($_SERVER);
//    echo '</pre>';
//    die();
    $outstanding = array();
    $future=array();
    $fact = array();
    $total = array();
    $regions = array();
    $maxAction = array();
    $today = new DateTime();
    $mkToday = dol_mktime(0,0,0,$today->format('m'),$today->format('d'),$today->format('Y'));
    $count = 0;
    $sql = "select responsibility.alias, resp2.alias as alias2 from llx_user
        left join responsibility on responsibility.rowid = llx_user.respon_id
        left join responsibility resp2 on resp2.rowid = llx_user.respon_id2
        where llx_user.rowid = ".$id_usr;

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);

    $respon_alias = array();
    if($db->num_rows($res)>0){
        $obj = $db->fetch_object($res);
        if(!empty($obj->alias))
            $respon_alias[]=$obj->alias;
        if(!empty($obj->alias2))
            $respon_alias[]=$obj->alias2;
    }
//    echo '<pre>';
//    var_dump($actions);
//    echo '</pre>';
//    die();
    foreach($actions as $item){

        if($item["id_usr"]==$id_usr&&$item["code"]=='AC_CUST'&&in_array($item["respon_alias"],$respon_alias)){
//            if($item["socid"]==9963) {
//                var_dump($item);
//                die();
//            }
            if(!in_array(empty($item["region_id"])?'null':$item["region_id"], $regions))
                $regions[]=empty($item["region_id"])?'null':$item["region_id"];
            $date = new DateTime($item["datep"]);
            $mkDate = dol_mktime(0, 0, 0, $date->format('m'), $date->format('d'), $date->format('Y'));
//            echo $item["datep"].' '.var_dump($mkDate >= $mkToday).'</br>';

//            if($item["datep"]=='2016-05-11'){
//                die('test');
//            }
            if ($mkDate == $mkToday)
                $count++;
            if ($mkDate >= $mkToday) {
                $future[$item["region_id"]][$item["datep"]]++;
                if ($mkDate - $mkToday <= 604800)//604800 sec by week
                    $future[$item["region_id"]]['week']++;
                if ($mkDate - $mkToday <= 2678400)//2678400 sec by month
                    $future[$item["region_id"]]['month']++;
            }

            if($mkDate < $mkToday && $item['percent'] != 100){//Додав $mkDate < $mkToday. Вважається логічним, щоб кількість прострочених рахувати, коли завдання повинно вже було бути виконано
                $outstanding[$item["region_id"]]++;
            }
            if($item['percent'] == 100 && (in_array($item['code'], $actioncode)||$item['callstatus']=='5')){
                $fact[$item["region_id"]][$item["datep"]]++;
                if($mkToday-$mkDate<=604800)//604800 sec by week
                    $fact[$item["region_id"]]['week']++;
                if($mkToday-$mkDate<=2678400)//2678400 sec by month
                    $fact[$item["region_id"]]['month']++;
            }

            $total[$item["region_id"]][$item["datep"]]++;
            if($mkToday-$mkDate<=604800)//604800 sec by week
                $total[$item["region_id"]]['week']++;
            if($mkToday-$mkDate<=2678400)//2678400 sec by month
                $total[$item["region_id"]]['month']++;
        }
    }
//    echo '<pre>';
//    var_dump($fact);
//    echo '</pre>';
//    die();
    $lineactive = getLineActive($id_usr);
    $lineactive[0]=array("name"=> "Напрямок не вказано", "type"=>"");

//    echo '<pre>';
//    var_dump($lineactive);
//    echo '</pre>';
//    die();
//    $sql = "select `regions`.`rowid`,`states`.`name` statename, `regions`.`name` from `regions`
//        inner join `states` on `states`.`rowid` = `regions`.`state_id`
//        where (`regions`.`rowid` in (select `llx_user_regions`.fk_id from `llx_user_regions` where `llx_user_regions`.fk_user = ".$id_usr."
//              and `llx_user_regions`.active = 1)
//              or `regions`.`rowid` in (".(count($regions)>0?implode(",",$regions):0)."))
//        and `regions`.active = 1";
//    if(count($regions)>0 && in_array('null', $regions))
//        $sql.=" union select null, 'Район', 'не вказано'";
//    $sql.=" order by statename, `name`";

    $out = '';
//    $res = $db->query($sql);
//    if(!$res)
//        dol_print_error($db);
//    while($obj = $db->fetch_object($res)){
    $userFilter = '';
    if($id_usr != $user->id)
        $userFilter='&id_usr='.$id_usr;

    foreach(array_keys($lineactive) as $key){
        $out.='<tr id="reg'.$key.'" class="regions subtype middle_size '.$_REQUEST['class'].'">';
        if(!empty($key)) {
            $out .= '<td colspan="2"><a href="/dolibarr/htdocs/responsibility/purchase/area.php?idmenu=10425&filter=&mainmenu=area&leftmenu='.$userFilter.'&lineactive=' . $key . '" target="_blank">' . $lineactive[$key]['name'] . '</a></td>';
            if($_SERVER["PHP_SELF"] == "/dolibarr/htdocs/responsibility/gen_dir/day_plan.php")
                $out .= '<td></td>';
        }else{
            $out .= '<td colspan="2">'. $lineactive[$key]['name'] . '</td>';
            if($_SERVER["PHP_SELF"] == "/dolibarr/htdocs/responsibility/gen_dir/day_plan.php")
                $out .= '<td></td>';
        }
//        $out.='<td></td>';
        //відсоток виконання
        if(isset($total[$key]['month'])){
            $value = round($fact[$key]['month']/$total[$key]['month']*100,0);
            $out.='<td style="text-align: center">'.$value.'</td>';
        }else
            $out.='<td></td>';
        if(isset($total[$key]['week'])){
            $value = round($fact[$key]['week']/$total[$key]['week']*100,0);
            $out.='<td style="text-align: center">'.$value.'</td>';
        }else
            $out.='<td></td>';
        for($i=6;$i>=0;$i--) {
            if(isset($total[$key][date("Y-m-d", (time()-3600*24*$i))])){
                $value = round($fact[$key][date("Y-m-d", (time()-3600*24*$i))]/$total[$key][date("Y-m-d", (time()-3600*24*$i))]*100,0);
                $out.='<td style="text-align: center">'.$value.'</td>';
            }else
                $out .= '<td></td>';
        }
        //фактично виконано
//        echo '<pre>';
//        var_dump($key, $fact[$key]);
//        echo '</pre>';
//        die();
        if(isset($fact[$key]['month']))
                $out.='<td style="text-align: center">'.$fact[$key]['month'].'</td>';
            else
                $out.='<td></td>';
        if(isset($fact[$key]['week']))
                $out.='<td style="text-align: center">'.$fact[$key]['week'].'</td>';
            else
                $out.='<td></td>';
        for($i=6;$i>=0;$i--){
            if(isset($fact[$key][date("Y-m-d", (time()-3600*24*$i))]))
                $out.='<td style="text-align: center">'.$fact[$key][date("Y-m-d", (time()-3600*24*$i))].'</td>';
            else
                $out.='<td style="text-align: center"></td>';
        }
        //прострочено
        if(isset($outstanding[$key])) {
            $out .= '<td id="outstanding'.$key.'" style="text-align: center; cursor: pointer;" onclick="ShowOutStandingRegion('.$key.', '.$id_usr.');">' . $outstanding[$key] . '</td>';
        }else
            $out.='<td></td>';
        //заплановано на майбутнє
        for($i=0;$i<=6;$i++){
            if($future[$key][date("Y-m-d", (time()+3600*24*$i))])
                $out.='<td style="text-align: center">'.$future[$key][date("Y-m-d", (time()+3600*24*$i))].'</td>';
            else
                $out.='<td></td>';
        }
        if(isset($future[$key]['week']))
                $out.='<td style="text-align: center">'.$future[$key]['week'].'</td>';
            else
                $out.='<td></td>';
        if(isset($future[$key]['month']))
                $out.='<td style="text-align: center">'.$future[$key]['month'].'</td>';
            else
                $out.='<td></td>';
        $out.='</tr>';
    }
    return $out;
}
function getLineActive($id_usr){
    global $db;
    //Визначаю сферу відповідальності користувача
    $sql = "select res1.alias, res2.alias alias2 from llx_user
        left join responsibility res1 on res1.rowid = llx_user.respon_id
        left join responsibility res2 on res1.rowid = llx_user.respon_id2
        where llx_user.rowid = ".$id_usr;
    $resAlias = $db->query($sql);
    $objAlias = $db->fetch_object($resAlias);
    $arrayAlias = array($objAlias->alias, $objAlias->alias2);
    $lineactive = array();
    if(count(array_intersect($arrayAlias, array('marketing')))) {
        $sql = "select distinct `llx_c_lineactive_marketing`.`rowid`, `llx_c_lineactive_marketing`.`name` from llx_c_lineactive_marketing
            inner join `llx_user_lineactive` on `llx_user_lineactive`.`fk_lineactive` = llx_c_lineactive_marketing.rowid
            where 1
            and `llx_user_lineactive`.`fk_user` = ".$id_usr."
            and `llx_user_lineactive`.`active` = 1
            and `llx_user_lineactive`.`page` is null";
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while ($obj = $db->fetch_object($res)) {
            if (!isset($lineactive[$obj->rowid])) {
                $lineactive[$obj->rowid] = array('name' => $obj->name);
            }
        }
    }


    if(count(array_intersect($arrayAlias, array('jurist', 'corp_manager', 'paperwork','counter','logistika')))) {
        $sql = "select category_counterparty.rowid, category_counterparty.name from `llx_user_categories_contractor`
            inner join `category_counterparty` on `category_counterparty`.`rowid` = `llx_user_categories_contractor`.`fk_categories`
            where `llx_user_categories_contractor`.fk_user = " . $id_usr . "
            and `llx_user_categories_contractor`.`active` = 1";
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while ($obj = $db->fetch_object($res)) {
            if (!isset($lineactive[$obj->rowid])) {
                $lineactive[$obj->rowid] = array('name' => $obj->name);
            }
        }
    }

    if(count(array_intersect($arrayAlias, array('purchase','service')))) {
        $sql = "select fk_lineactive, `oc_category_description`.`name`, min(page) page from `llx_user_lineactive`
        inner join `oc_category_description` on `oc_category_description`.`category_id` = `llx_user_lineactive`.fk_lineactive
        where llx_user_lineactive.fk_user = " . $id_usr . "
        and llx_user_lineactive.active = 1
        and oc_category_description.`language_id` = 4
        group by fk_lineactive, `oc_category_description`.`name`";
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while ($obj = $db->fetch_object($res)) {
            if (!isset($lineactive[$obj->fk_lineactive])) {
                switch ($obj->page) {
                    case 1: {
                        $type = 'Ціле';
                    }
                        break;
                    case 2: {
                        $type = 'Унік.з/ч';
                    }
                        break;
                    case 3: {
                        $type = 'Станд.вир';
                    }
                        break;
                }
                $lineactive[$obj->fk_lineactive] = array('name' => $obj->name, 'type' => $type);
            }
        }
    }
    return $lineactive;
}
function GetBestUserID(){
    global $CustActions,$user;

    $maxCount = 0;
    $id_usr = 0;

    foreach(array_keys($CustActions) as $userID){
        if($maxCount<$CustActions[$userID]){
            $maxCount = $CustActions[$userID];
            $id_usr = $userID;
        }
    }
    return $id_usr;
}