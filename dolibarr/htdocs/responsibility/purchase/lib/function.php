<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 30.07.2016
 * Time: 16:24
 */
function getLineActive($id_usr){
    global $db;
    $sql = "select fk_lineactive, `oc_category_description`.`name`, min(page) page from `llx_user_lineactive`
        inner join `oc_category_description` on `oc_category_description`.`category_id` = `llx_user_lineactive`.fk_lineactive
        where llx_user_lineactive.fk_user = ".$id_usr."
        and llx_user_lineactive.active = 1
        and oc_category_description.`language_id` = 4
        group by fk_lineactive, `oc_category_description`.`name`";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $lineactive = array();
    while($obj = $db->fetch_object($res)){
        if(!isset($lineactive[$obj->fk_lineactive])) {
            switch($obj->page){
                case 1:{
                    $type = 'Ціле';
                }break;
                case 2:{
                    $type = 'Унік.з/ч';
                }break;
                case 3:{
                    $type = 'Станд.вир';
                }break;
            }
            $lineactive[$obj->fk_lineactive] = array('name' => $obj->name, 'type'=>$type);
        }
    }
    return $lineactive;
}
function getSubLineActive($lineactive=array()){
    global $db;
    if(count($lineactive)==0)
        $lineactive[0][]=0;
    $sql = "select path_id, category_id from `oc_category_path` where path_id in (".implode(',',$lineactive).")";
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