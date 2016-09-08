<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 18.08.2016
 * Time: 9:34
 */

require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
if(isset($_REQUEST['action'])){
    if($_REQUEST['action']=='getRaportData'){
        switch($_REQUEST['name']){
            case 'states':{
                echo json_encode(getRapostByResponding($_REQUEST['respon_id'], $_REQUEST['val']));
            }break;
            case 'respon_id':{
                echo json_encode(getRapostByResponding($_REQUEST['val']));
            }break;
        }
    }
    exit();
}

function getRapostByResponding($respon_id, $state_id = 0){
    global $db;
    $sql = "select alias from responsibility where rowid = ".$respon_id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $out = array();
    switch($obj->alias) {
        case 'sale': {//Продажі
            $sql = "select region_id, count(*) iCount, sum(`llx_societe_classificator`.`value`) sumSquare
                    from llx_societe
                    left join `llx_societe_classificator` on `llx_societe_classificator`.`soc_id` = `llx_societe`.`rowid`
                      where `categoryofcustomer_id` in (
                      select fx_category_counterparty from `responsibility_param`
                      where fx_responsibility = " . $respon_id . ")
                    group by region_id;";
            $resTMP = $db->query($sql);
            $custCount = array();
            while ($objCount = $db->fetch_object($resTMP)) {
                $custCount[empty($objCount->region_id)?0:$objCount->region_id] = array('count' => $objCount->iCount, 'sumSuare' => $objCount->sumSquare);
            }
            $sql = "select `regions_id`,`classifycation_id`,`value` from `regions_param`";
            $resParam = $db->query($sql);
            if (!$resParam)
                dol_print_error($db);
            $regionParam = array();
            while ($objParam = $db->fetch_object($resParam)) {
                $regionParam[$objParam->regions_id][$objParam->classifycation_id] = $objParam->value;
            }
            $dirID = array(13,18,19,27,31,36,37,41);//Директори
            $engineerID = array(20,28,35);//Інженера
            $agronomistID = array(21,40);//Агрономи
            $zootechnicianID = array(22);//Зоотехніки            
            $counterID = array(24,42);//Бухгалтери            
            $sql = "select case when region_id is null then 0 else region_id end region_id, llx_societe.rowid socid, post_id, `llx_societe_classificator`.`value` Square
                from llx_societe
                left join `llx_societe_classificator` on `llx_societe_classificator`.`soc_id` = `llx_societe`.`rowid`
                left join `llx_societe_contact` on `llx_societe_contact`.`socid` = `llx_societe`.`rowid`
                where 1
                and llx_societe.active = 1
                and `llx_societe_contact`.active = 1
                and `llx_societe_contact`.post_id in(13,18,19,20,21,22,24,27,28,31,35,36,37,40,41,42)
                and `categoryofcustomer_id` in (
                select fx_category_counterparty from `responsibility_param`
                where fx_responsibility = 1)";
//            $start = time();
            $resPost = $db->query($sql);

            $postParam = array();

            while($objPost = $db->fetch_object($resPost)){
                if(in_array($objPost->post_id, $dirID)){//Директори
                    if(!isset($postParam[$objPost->region_id][$dirID[0]]))
                        $postParam[$objPost->region_id][$dirID[0]] = array('count' => 1, 'Square' => $objPost->Square, 'socID'=>array($objPost->socid));
                    else {
                        $postParam[$objPost->region_id][$dirID[0]]['count'] += 1;
                        if(!in_array($objPost->socid, $postParam[$objPost->region_id][$dirID[0]]['socID'])){
                            $postParam[$objPost->region_id][$dirID[0]]['Square'] += $objPost->Square;
                            $postParam[$objPost->region_id][$dirID[0]]['socID'][] = $objPost->socid;
                        }
                    }
                }
                if(in_array($objPost->post_id, $engineerID)){//Інженери
                    if(!isset($postParam[$objPost->region_id][$engineerID[0]]))
                        $postParam[$objPost->region_id][$engineerID[0]] = array('count' => 1, 'Square' => $objPost->Square, 'socID'=>array($objPost->socid));
                    else {
                        $postParam[$objPost->region_id][$engineerID[0]]['count'] += 1;
                        if(!in_array($objPost->socid, $postParam[$objPost->region_id][$engineerID[0]]['socID'])){
                            $postParam[$objPost->region_id][$engineerID[0]]['Square'] += $objPost->Square;
                            $postParam[$objPost->region_id][$engineerID[0]]['socID'][] = $objPost->socid;
                        }
                    }
                }
                if(in_array($objPost->post_id, $agronomistID)){//Агрономи
                    if(!isset($postParam[$objPost->region_id][$agronomistID[0]]))
                        $postParam[$objPost->region_id][$agronomistID[0]] = array('count' => 1, 'Square' => $objPost->Square, 'socID'=>array($objPost->socid));
                    else {
                        $postParam[$objPost->region_id][$agronomistID[0]]['count'] += 1;
                        if(!in_array($objPost->socid, $postParam[$objPost->region_id][$agronomistID[0]]['socID'])){
                            $postParam[$objPost->region_id][$agronomistID[0]]['Square'] += $objPost->Square;
                            $postParam[$objPost->region_id][$agronomistID[0]]['socID'][] = $objPost->socid;
                        }
                    }
                }
                if(in_array($objPost->post_id, $zootechnicianID)){//Зоотехніки
                    if(!isset($postParam[$objPost->region_id][$zootechnicianID[0]]))
                        $postParam[$objPost->region_id][$zootechnicianID[0]] = array('count' => 1, 'Square' => $objPost->Square, 'socID'=>array($objPost->socid));
                    else {
                        $postParam[$objPost->region_id][$zootechnicianID[0]]['count'] += 1;
                        if(!in_array($objPost->socid, $postParam[$objPost->region_id][$zootechnicianID[0]]['socID'])){
                            $postParam[$objPost->region_id][$zootechnicianID[0]]['Square'] += $objPost->Square;
                            $postParam[$objPost->region_id][$zootechnicianID[0]]['socID'][] = $objPost->socid;
                        }
                    }
                }
                if(in_array($objPost->post_id, $counterID)){//Бухгалтери
                    if(!isset($postParam[$objPost->region_id][$counterID[0]]))
                        $postParam[$objPost->region_id][$counterID[0]] = array('count' => 1, 'Square' => $objPost->Square, 'socID'=>array($objPost->socid));
                    else {
                        $postParam[$objPost->region_id][$counterID[0]]['count'] += 1;
                        if(!in_array($objPost->socid, $postParam[$objPost->region_id][$counterID[0]]['socID'])){
                            $postParam[$objPost->region_id][$counterID[0]]['Square'] += $objPost->Square;
                            $postParam[$objPost->region_id][$counterID[0]]['socID'][] = $objPost->socid;
                        }
                    }
                }
            }
//            $time = time()-$start;
//            var_dump($time);
////            die();
//            echo '<pre>';
//            var_dump($postParam[2]);
//            echo '</pre>';
//            die();

            $sql = "select regions.rowid, states.`name` state_name, regions.`name` region_name
                    from regions
                    inner join states on states.rowid = regions.state_id
                    where 1 and regions.`active` = 1";
            if ($state_id == 0)
                $sql .= " union select 0, null, 'Район не вказано'";
            else
                $sql .= " and states.rowid = " . $state_id;
            $sql .= " order by state_name, region_name";
//            echo '<pre>';
//            var_dump($sql);
//            echo '</pre>';
//            die();

            $resBody = $db->query($sql);
            if (!$resBody)
                dol_print_error($db);
            $tbody = '';
            $num = 0;
            while ($objBody = $db->fetch_object($resBody)) {
                $class = fmod($num, 2) != 1 ? ("impair") : ("pair");
                $tbody .= '<tr region_id="' . $objBody->rowid . '" class="middle_size ' . $class . '">';
                $tbody .= '<td>' . $objBody->state_name . '</td>';
                $tbody .= '<td>' . $objBody->region_name . '</td>';
                $tbody .= '<td></td>';
                $tbody .= '<td>' . $custCount[empty($objBody->rowid)?0:$objBody->rowid]['count'] . '</td>';
                $tbody .= '<td></td>';
                $tbody .= '<td>' .$regionParam[$objBody->rowid][3] . '</td>';
                $tbody .= '<td>' .$regionParam[$objBody->rowid][4] . '</td>';
                $tbody .= '<td>' .$custCount[empty($objBody->rowid)?0:$objBody->rowid]['sumSuare'] . '</td>';
                $percent = 0;
                if(!empty($regionParam[$objBody->rowid][4])&&$regionParam[$objBody->rowid][4] != 0){
                    $percent = round($custCount[$objBody->rowid]['sumSuare']*100/$regionParam[$objBody->rowid][4]);
                }
                if($percent<50){
                    $color = 'rgb(255, 0, 0)';
                    $fontcolor = '#fff';
                }elseif($percent>=50&&$percent<=75){
                    $color = 'rgb(255, 153, 0)';
                    $fontcolor = '#000';
                }else {
                    $color = 'rgb(0, 255, 0)';
                    $fontcolor = '#000';
                }
                $tbody .= '<td style="background:'.$color.';color:'.$fontcolor.'">' . $percent . '%</td>';
                $tbody .= '<td></td>';
                //Розрахунок по директорам

                $count = $postParam[empty($objBody->rowid)?0:$objBody->rowid][$dirID[0]]['count'];
                $Square = $postParam[empty($objBody->rowid)?0:$objBody->rowid][$dirID[0]]['Square'];

                $tbody .= '<td>'.$count.'</td>';
                $tbody .= '<td>'.$Square.'</td>';
                $percent = 0;
                if(!empty($regionParam[$objBody->rowid][4])&&$regionParam[$objBody->rowid][4] != 0){
                    $percent = round($Square*100/$regionParam[$objBody->rowid][4]);
                }
                if($percent<50){
                    $color = 'rgb(255, 0, 0)';
                    $fontcolor = '#fff';
                }elseif($percent>=50&&$percent<=75){
                    $color = 'rgb(255, 153, 0)';
                    $fontcolor = '#000';
                }else {
                    $color = 'rgb(0, 255, 0)';
                    $fontcolor = '#000';
                }
                $tbody .= '<td style="background:'.$color.';color:'.$fontcolor.'">' . $percent . '%</td>';
                $tbody .= '<td></td>';
                //Розрахунок по інженерам

                $count = $postParam[empty($objBody->rowid)?0:$objBody->rowid][$engineerID[0]]['count'];
                $Square = $postParam[empty($objBody->rowid)?0:$objBody->rowid][$engineerID[0]]['Square'];

                $tbody .= '<td>'.$count.'</td>';
                $tbody .= '<td>'.$Square.'</td>';
                $percent = 0;
                if(!empty($regionParam[$objBody->rowid][4])&&$regionParam[$objBody->rowid][4] != 0){
                    $percent = round($Square*100/$regionParam[$objBody->rowid][4]);
                }
                if($percent<50){
                    $color = 'rgb(255, 0, 0)';
                    $fontcolor = '#fff';
                }elseif($percent>=50&&$percent<=75){
                    $color = 'rgb(255, 153, 0)';
                    $fontcolor = '#000';
                }else {
                    $color = 'rgb(0, 255, 0)';
                    $fontcolor = '#000';
                }
                $tbody .= '<td style="background:'.$color.';color:'.$fontcolor.'">' . $percent . '%</td>';
                $tbody .= '<td></td>';
                //Розрахунок по інженерам

                $count = $postParam[empty($objBody->rowid)?0:$objBody->rowid][$agronomistID[0]]['count'];
                $Square = $postParam[empty($objBody->rowid)?0:$objBody->rowid][$agronomistID[0]]['Square'];

                $tbody .= '<td>'.$count.'</td>';
                $tbody .= '<td>'.$Square.'</td>';
                $percent = 0;
                if(!empty($regionParam[$objBody->rowid][4])&&$regionParam[$objBody->rowid][4] != 0){
                    $percent = round($Square*100/$regionParam[$objBody->rowid][4]);
                }
                if($percent<50){
                    $color = 'rgb(255, 0, 0)';
                    $fontcolor = '#fff';
                }elseif($percent>=50&&$percent<=75){
                    $color = 'rgb(255, 153, 0)';
                    $fontcolor = '#000';
                }else {
                    $color = 'rgb(0, 255, 0)';
                    $fontcolor = '#000';
                }
                $tbody .= '<td style="background:'.$color.';color:'.$fontcolor.'">' . $percent . '%</td>';
                $tbody .= '<td></td>';
                //Розрахунок по зоотехніки

                $count = $postParam[empty($objBody->rowid)?0:$objBody->rowid][$zootechnicianID[0]]['count'];
                $Square = $postParam[empty($objBody->rowid)?0:$objBody->rowid][$zootechnicianID[0]]['Square'];

                $tbody .= '<td>'.$count.'</td>';
                $tbody .= '<td>'.$Square.'</td>';
                $percent = 0;
                if(!empty($regionParam[$objBody->rowid][4])&&$regionParam[$objBody->rowid][4] != 0){
                    $percent = round($Square*100/$regionParam[$objBody->rowid][4]);
                }
                if($percent<50){
                    $color = 'rgb(255, 0, 0)';
                    $fontcolor = '#fff';
                }elseif($percent>=50&&$percent<=75){
                    $color = 'rgb(255, 153, 0)';
                    $fontcolor = '#000';
                }else {
                    $color = 'rgb(0, 255, 0)';
                    $fontcolor = '#000';
                }
                $tbody .= '<td style="background:'.$color.';color:'.$fontcolor.'">' . $percent . '%</td>';
                $tbody .= '<td></td>';
                //Розрахунок по бухгалтери

                $count = $postParam[empty($objBody->rowid)?0:$objBody->rowid][$counterID[0]]['count'];
                $Square = $postParam[empty($objBody->rowid)?0:$objBody->rowid][$counterID[0]]['Square'];

                $tbody .= '<td>'.$count.'</td>';
                $tbody .= '<td>'.$Square.'</td>';
                $percent = 0;
                if(!empty($regionParam[$objBody->rowid][4])&&$regionParam[$objBody->rowid][4] != 0){
                    $percent = round($Square*100/$regionParam[$objBody->rowid][4]);
                }
                if($percent<50){
                    $color = 'rgb(255, 0, 0)';
                    $fontcolor = '#fff';
                }elseif($percent>=50&&$percent<=75){
                    $color = 'rgb(255, 153, 0)';
                    $fontcolor = '#000';
                }else {
                    $color = 'rgb(0, 255, 0)';
                    $fontcolor = '#000';
                }
                $tbody .= '<td style="background:'.$color.';color:'.$fontcolor.'">' . $percent . '%</td>';
                $tbody .= '<td></td>';
                $tbody .= '<td></td>';
                $num++;

//            $tbody.='';
            }
            $sql = "select 0 rowid, '' `name`
                union
                select rowid, `name` from states
                where active = 1
                order by `name`";
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            $state = '';
            while($obj = $db->fetch_object($res)){
                $state.='<option value="'.$obj->rowid.'">'.$obj->name.'</option>';
            }
            $out = array('tbody' => $tbody, 'state'=> $state);
        }break;
    }
    return $out;
}