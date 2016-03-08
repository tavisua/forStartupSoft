<?php
require '../main.inc.php';
//    echo '<pre>';
//    var_dump($_SERVER);
//    echo '</pre>';
//    die();

if(isset($_POST['action'])&&$_POST['action']=="addquestion"&&mb_strlen(trim($_POST['newquestion']))>0){
    unset($_POST);
    global $db;

    $sql='insert into llx_c_category_product_question(category_id,question,page,active,id_usr)
    values('.$_REQUEST['id_cat'].', "'.$db->escape(trim($_REQUEST['newquestion'])).'",'.$_REQUEST['numpage'].',1,'.$user->id.')';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
}elseif(isset($_POST['action'])&&$_POST['action']=="deletequestion"){
    global $db;
    $sql='update llx_c_category_product_question set active=0, id_usr='.$user->id.'
    where rowid='.$_POST['rowid'];
        $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
}
if(isset($_REQUEST['id_cat'])&&$_REQUEST['id_cat']>0){
    unset($_POST);
    $GroupProductManager = $langs->trans("GroupProductManager");
    llxHeader("",$GroupProductManager,"");
    print_fiche_titre($GroupProductManager);
    $form = new Form($db);
    $sql = 'select name from oc_category_description where category_id='.$_REQUEST['id_cat'].' and language_id=4';

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $groupname = $obj->name;
    $page=isset($_REQUEST['page'])?(empty($_REQUEST['page'])?1:$_REQUEST['page']):1;
    $Question = GetQuestion($_REQUEST['id_cat'], $page);
    ob_start();
    include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/products/addgroupproduct.html');
    echo ob_get_clean();
    exit();

exit();
}

$GroupProductManager = $langs->trans("GroupPriceList");
llxHeader("",$GroupProductManager,"");
print_fiche_titre($GroupProductManager);
$form = new Form($db);
$Categories = ShowCategories();
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/products/groupproduct_manager.html');
echo ob_get_clean();
exit();

function GetQuestion($id_cat, $page=1){
    global $db;
    $sql = 'select rowid, question from `llx_c_category_product_question`
    where category_id='.$id_cat.' and page='.$page.'
    and active = 1';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '';
    $nom = 0;
    $class=(fmod($nom++,2)==0?"impair":"pair");
    $out.='<form id = "addquestion" action="'.$_SERVER["REQUEST_URI"].'" method="post">
                <input id="questionaction" type="hidden" value="" name="action">
                <input id="id_cat" type="hidden" value="'.$id_cat.'" name="id_cat">
                <input id="questionid" type="hidden" value="" name="rowid">
                <input id="pagequestion" type="hidden" value="'.$_SERVER["page"].'" name="numpage">
                <input id="mainmenu" type="hidden" value="'.$_REQUEST['mainmenu'].'" name="mainmenu">
                <tr class="'.$class.'">
                    <td style="width:400px"><input id="newquestion" name="newquestion" type="text" value=""  maxlength="128" size="80"></td>
                    <td style="width:30px;text-align: center" onclick="addquestion();"><img src="/dolibarr/htdocs/theme/eldy/img/Add.png"></td>
                </tr>
            </form>';
    while($obj = $db->fetch_object($res)){
        $class=(fmod($nom++,2)==0?"impair":"pair");
        $out .='<tr class="'.$class.' middle_size">
            <td id="'.$obj->rowid.'">'.$obj->question.'</td>
            <td onclick=deleteitem('.$obj->rowid.') style="width:30px;text-align: center"><img border="0" title="Delete" alt="" src="/dolibarr/htdocs/theme/eldy/img/delete.png"></td>
        </tr>';

    }
    $out .='';
    return $out;

}
function ShowCategories($showfirstcategory_id = false){
    global $db, $langs;
    $form = new Form($db);
    $sql = "SELECT DISTINCT c.category_id, c.parent_id, cd2.name,  (SELECT  GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR ' &gt; ')
        FROM oc_category_path cp LEFT JOIN oc_category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = 4 GROUP BY cp.category_id) AS path
        FROM oc_category c
        LEFT JOIN oc_category_description cd2 ON (c.category_id = cd2.category_id)
        WHERE c.parent_id <> 0 AND cd2.language_id = 4
        order by parent_id, c.sort_order, cd2.name";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $basic_group = array();
    $group = array();
    $categries = array();
    while($obj = $db->fetch_array($res)){
        if($obj['parent_id'] == 67){
            $basic_group[]=$obj['category_id'];
        }
        if(!in_array($obj['parent_id'], $group))
            $group[]=$obj['parent_id'];
        $categries[$obj['category_id']]=array($obj['name'], $form->SymbolCounter('&gt;', $obj['path']));

    }
    //Subcategory
    $sub_category = array();
    foreach($group as $group_id){
        mysqli_data_seek($res, 0);
        $subcatstr='';
        while($obj = $db->fetch_object($res)){
            if($obj->parent_id == $group_id){
                if(empty($subcatstr))
                    $subcatstr = $obj->category_id;
                else
                    $subcatstr.=','.$obj->category_id;
            }
        }
        $sub_category[$group_id]=explode(',', $subcatstr);
    }
    $out = '';
    $index = 0;
    $id_cat = $_REQUEST['id_cat'];
    while(count($basic_group)) {
        $catalog_id = $basic_group[0];
        array_shift($basic_group);
        if(isset($sub_category[$catalog_id]) || $categries[$catalog_id][1] == 1) {
            $out .= '<ul '.($categries[$catalog_id][1]!=0?'class="subcatalog"':'').'><a href="' .$_SERVER['PHP_SELF'].'?mainmenu=tools&id_cat='.$catalog_id.'#cat'.$catalog_id.'">' .$categries[$catalog_id][0] . '</a></ul>';
            for($i=count($sub_category[$catalog_id])-1; $i>=0; $i--) {
                array_unshift($basic_group, $sub_category[$catalog_id][$i]);
            }
        }else {
            if(empty($id_cat)) {
                if($showfirstcategory_id)
                    return $catalog_id;
                $id_cat = $catalog_id;
                $out .= '<li id="cat'.$id_cat.'" class="selected"><a href="' .$_SERVER['PHP_SELF'].'?mainmenu=tools&id_cat='.$id_cat.'#cat'.$id_cat.'">'.$categries[$catalog_id][0] . '</a></li>';
            }else{
                $out .= '<li id="cat'.$catalog_id.'" '.($id_cat==$catalog_id?'class="selected"':'').'><a href="' .$_SERVER['PHP_SELF'].'?mainmenu=tools&id_cat='.$catalog_id.'#cat'.$catalog_id.'">'.$categries[$catalog_id][0] . '</a></li>';
            }
        }
        $index++;
    }

    return $out;
}