<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 23.11.2016
 * Time: 5:22
 */
require '../main.inc.php';

if($_REQUEST['action'] == 'setsubdivmentor'){

    $sql = "select count(*) iCount from llx_user_subdiv_mentor where fk_user = ".$_REQUEST['id_usr'].' and fk_id = '.$_REQUEST['subdiv_id'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
//    var_dump($obj->iCount>0);
//    die();
    if($obj->iCount!=0){
        $sql = "update llx_user_subdiv_mentor ";
        if($_REQUEST['flag']=='true')
            $sql.=" set active = 1,";
        else
            $sql.=" set active = 0,";
        $sql.=" dtChange = Now() where fk_user = ".$_REQUEST['id_usr'].' and fk_id = '.$_REQUEST['subdiv_id'];
    }else{
        $sql = "insert into llx_user_subdiv_mentor(fk_user,fk_id,active,id_usr,dtChange) values(".
            $_REQUEST['id_usr'].",".$_REQUEST['subdiv_id'].",1,".$user->id.",Now())";
    }
//    die($sql);
    $res = $db->query($sql);
    if(!$res) {
//        dol_print_error($db);
        echo 0;
    }else
        echo 1;
//    echo '<pre>';
//    var_dump((bool)$_REQUEST['flag']);
//    echo '</pre>';
    exit();
}
if (! $user->rights->user->user->creer) accessforbidden();
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';

$form = new Form($db);
$formother=new FormOther($db);
$object = new User($db);
$id_usr = $_REQUEST['id'];
$object->fetch($id_usr);

llxHeader('',$langs->trans("SubdivisionsMentor"));
print_fiche_titre($langs->trans("SubdivisionsMentor"));
print '<a href="/dolibarr/htdocs/core/tools.php?mainmenu=tools&idmenu=5223">'.$langs->trans("Tools").'</a>  /
    <a href="/dolibarr/htdocs/core/users_and_group/groups_manager.php?mainmenu=tools">'.$langs->trans('MenuUsersAndGroups').'</a>/
    <a href="/dolibarr/htdocs/core/users_and_group/groups_manager.php?mainmenu=tools">'.$langs->trans('Users').'</a> /
    <a href="/dolibarr/htdocs/user/card.php?id='.$id.'&mainmenu=tools">'.$object->lastname.' '.$object->firstname.'</a>';

$object->getrights();

// Show tabs
$head = user_prepare_head($object);
$title = $langs->trans("User");
dol_fiche_head($head, 'SubdivisionsMentor', $title, 0, 'user');
print ShowSubdivisions($id_usr);
print '<script type="application/javascript">
    var searchParam={};
    $(document).ready(function() {   
        var imgs = $("img[id*='.("'mentor_id'").']");  
        $.each(imgs, function(index, values){              
            $("#"+values.id).attr("onclick", "SetMentor($(this))");
//            console.log($("#"+values.id).attr("onclick"));
        })
        var search = location.search.substr(1);
        var searchArray = search.split("&");        
        $.each(searchArray, function(key,value){
            searchParam[value.substr(0,strpos(value,"="))] = value.substr(strpos(value,"=")+1)
        });        
//        $("img").click(setMentor);
    })
    function SetMentor(elem){
        var subdiv_id = elem.parent().parent().attr("id").substr(2);
        var param = {
            subdiv_id:subdiv_id,
            id_usr:searchParam["id"],
            action:"setsubdivmentor",
            flag: $("#img"+subdiv_id+"mentor_id").attr("src") == "/dolibarr/htdocs/theme/eldy/img/switch_off.png" 
        }
        $.ajax({
            data:param,
            cache:false,
            success:function(result){
                if(result == 1){
                    if(param["flag"])
                        $("#img"+subdiv_id+"mentor_id").attr("src", "/dolibarr/htdocs/theme/eldy/img/switch_on.png");
                    else
                        $("#img"+subdiv_id+"mentor_id").attr("src", "/dolibarr/htdocs/theme/eldy/img/switch_off.png")                    
                }
            }
        })
        console.log(param);
    }    
</script>';
exit();

function ShowSubdivisions($id_usr){
    global $langs,$user,$conf;
    $TableParam = array();
    $ColParam['title']=$langs->trans('Subdivision');
    $ColParam['width']='300';
    $ColParam['align']='';
    $ColParam['class']='';
    $TableParam[]=$ColParam;
    
    $ColParam['title']=$langs->trans('Active');
    $ColParam['width']='80';
    $ColParam['align']='';
    $ColParam['class']='';
    $TableParam[]=$ColParam;
    $setting = $user->getregions($id_usr);
    $tablename='llx_user_subdiv_mentor';
    include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
    $db_mysql = new dbBuilder();
    $sql = 'select `subdivision`.`rowid`, `subdivision`.`name`, IF(`llx_user_subdiv_mentor`.`active` = 1,TRUE,FALSE) mentor_id 
        from `subdivision` 
        left join (select rowid, fk_id,`active` from `llx_user_subdiv_mentor` where fk_user = '.$_REQUEST['id'].' and active = 1 )`llx_user_subdiv_mentor` on `llx_user_subdiv_mentor`.fk_id = `subdivision`.`rowid` 
        where `subdivision`.`active` = 1 
        order by `subdivision`.`name`';
    
    $table = $db_mysql->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, '', array(), $setting);
    return $table;
}