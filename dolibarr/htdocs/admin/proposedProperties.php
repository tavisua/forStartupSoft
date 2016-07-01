<?php
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/societecontact_class.php';
global $db,$user;
if(isset($_POST['action'])&&$_POST['action'] == 'OK'){
    $sql = "select fk_lineactive,fk_post,active from llx_proposition_properties where fk_proposition = ".$_REQUEST['proposed_id'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $inserted = array();
    while($obj = $db->fetch_object($res)){
        $inserted[$obj->fk_lineactive][$obj->fk_post]=$obj->active;
    }
    foreach(array_keys($inserted) as $fk_lineactive) {
        foreach(array_keys($inserted[$fk_lineactive]) as $fk_post){
            if(!in_array($fk_post, $_POST['fk_post'])){
                $sql = "update llx_proposition_properties set active = 0, id_usr=".$user->id." where
                fk_proposition = ".$_REQUEST['proposed_id']." and fk_lineactive = ".$fk_lineactive." and fk_post = ".$fk_post;
                $del_res = $db->query($sql);
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
                if(!$del_res)
                    dol_print_error($db);
                }
        }

    }
    foreach($_POST['fk_lineactive'] as $fk_lineactive){
        foreach($_POST['fk_post'] as $fk_post){
            if(!isset($inserted[$fk_lineactive][$fk_post])){
                $sql = "insert into llx_proposition_properties(fk_proposition,fk_lineactive,fk_post,active,id_usr)
                  values(".$_REQUEST['proposed_id'].",".$fk_lineactive.",".$fk_post.",1,".$user->id.")";
            }elseif(!$inserted[$fk_lineactive][$fk_post]){
                $sql = "update llx_proposition_properties set active = 1, id_usr=".$user->id." where fk_proposition = ".$_REQUEST['proposed_id']
                    ." and fk_lineactive=".$fk_lineactive." and fk_post = ".$fk_post;
            }
            $insert_res = $db->query($sql);
            if(!$insert_res)
                dol_print_error($db);
//            echo '<pre>';
//            var_dump($sql);
//            echo '</pre>';
//            die();
        }
    }
    unset($_POST);

    header("Location: ".DOL_URL_ROOT."/admin/dict.php?id=39#rowid-".$_REQUEST['proposed_id']);
    exit();
}elseif(isset($_POST['action'])){
    unset($_POST);
    header("Location: ".DOL_URL_ROOT."/admin/dict.php?id=39#rowid-".$_REQUEST['proposed_id']);
    exit();
}
//echo '<pre>';
//var_dump($_POST);
//echo '</pre>';
//die();

$sql ="select fk_lineactive,fk_post from llx_proposition_properties where fk_proposition = ".$_REQUEST['proposed_id'];
$sql.=" and active = 1";
$res = $db->query($sql);
if(!$res)
    dol_print_error($db);
$lineactive_array = array();
$post_array = array();
while($obj = $db->fetch_object($res)){
    $lineactive_array[]=$obj->fk_lineactive;
    $post_array[] = $obj->fk_post;
}
$formcompany = new FormCompany($db);
$lineactive = $formcompany->lineactiveCusomter(0, $lineactive_array, 10, 'fk_lineactive');
$societecontact = new societecontact($db);
$post = $societecontact->selectPost('fk_post', $post_array, 10);
llxHeader("", "Налаштування пропозиції", "");
include DOL_DOCUMENT_ROOT.'/theme/eldy/admin/proposedProperties.html';
llxPopupMenu();