<?php
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
//require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/core/class/html.form.class.php';

if(isset($_REQUEST['product_id'])){
    global $user;
    if(isset($_REQUEST['action'])){
        switch($_REQUEST['action']){
            case 'setlink':{
                if(isset($_REQUEST['spare_id']))
                    $tablename='`llx_spareparts`';
                elseif(isset($_REQUEST['working_units_id'])){
                    $tablename='`llx_workingunits`';
                }
                $sql = 'select rowid from '.$tablename.'
                  where product_pref = "'.$_REQUEST["product_pref"].'"
                  and product_id='.$_REQUEST["product_id"].'
                  and '.(isset($_REQUEST["spare_pref"])?'spare_pref':'working_units_pref').' = "'.(isset($_REQUEST["spare_pref"])?$_REQUEST["spare_pref"]:$_REQUEST["working_units_pref"]).'"
                  and '.(isset($_REQUEST["spare_id"])?'spare_id':'working_units_id').' = '.(isset($_REQUEST['spare_id'])?$_REQUEST["spare_id"]:$_REQUEST["working_units_id"]);
                $res = $db->query($sql);
                if(!$res)
                    dol_print_error($db);
                if($db->num_rows($res) == 0){
                    $sql = 'insert into '.$tablename.'(product_pref,product_id,'.(isset($_REQUEST['spare_id'])?'spare_pref,spare_id':'working_units_pref,working_units_id').',active,id_usr)
                    values("'.$_REQUEST["product_pref"].'",
                            '.$_REQUEST["product_id"].',
                           "'.(isset($_REQUEST["spare_pref"])?$_REQUEST["spare_pref"]:$_REQUEST["working_units_pref"]).'",
                            '.(isset($_REQUEST['spare_id'])?$_REQUEST["spare_id"]:$_REQUEST["working_units_id"]).', 1, '.$user->id.')';
                }else{
                    $obj = $db->fetch_object($res);
                    $sql = 'update '.$tablename.' set
                    product_pref = "'.$_REQUEST["product_pref"].'",
                    product_id = '.$_REQUEST["product_id"].',
                    '.(isset($_REQUEST["spare_pref"])?'spare_pref':'working_units_pref').' = "'.(isset($_REQUEST["spare_pref"])?$_REQUEST["spare_pref"]:$_REQUEST["working_units_pref"]).'",
                    '.(isset($_REQUEST["spare_id"])?'spare_id':'working_units_id').' = '.(isset($_REQUEST['spare_id'])?$_REQUEST["spare_id"]:$_REQUEST["working_units_id"]).',
                    active = 1,
                    id_usr = '.$user->id.'
                    where rowid= '.$obj->rowid;
                }
                $res = $db->query($sql);
                if(!$res)
                    dol_print_error($db);
                echo 'success_setlink';
                exit();
            }break;
            case 'unsetlink':{
                if(isset($_REQUEST['spare_id']))
                    $tablename='`llx_spareparts`';
                elseif(isset($_REQUEST['working_units_id'])){
                    $tablename='`llx_workingunits`';
                }
                $sql = 'select rowid from '.$tablename.'
                  where product_pref = "'.$_REQUEST["product_pref"].'"
                  and product_id='.$_REQUEST["product_id"].'
                  and spare_pref="'.$_REQUEST["spare_pref"].'"
                  and spare_id='.$_REQUEST["spare_id"];
                $res = $db->query($sql);
                if(!$res)
                    dol_print_error($db);
                if($db->num_rows($res) > 0){
                    $obj = $db->fetch_object($res);
                    $sql = 'update '.$tablename.' set
                    active = 0,
                    id_usr = '.$user->id.'
                    where rowid= '.$obj->rowid;
                    $res = $db->query($sql);
                    if(!$res)
                        dol_print_error($db);
                    echo 'success_unsetlink';
                }
                exit();
            }break;
        }
    }
    if($_REQUEST['product_id'] != 0)
        $ProductManager = $langs->trans("ProductManager");
    else
        $ProductManager = $langs->trans("NewProduct");
    llxHeader("",$ProductManager,"");
    print_fiche_titre($ProductManager);
    $sql = 'select `oc_product_description`.`name` from `oc_product_description` where `product_id` = '.$_REQUEST['product_id'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $productname = $obj->name;
    $form = new Form($db);
    $product_static = new Product($db);
    $id_cat = $_REQUEST['id_cat'];
    if(!isset($_REQUEST['id_cat'])){
        $sql = 'select category_id from oc_product_to_category where product_id='.$_REQUEST['product_id'];
        $cat_res = $db->query($sql);
        $obj = $db->fetch_object($cat_res);
        $id_cat = $obj->category_id;
    }
    $linkpage = $_SERVER["SCRIPT_NAME"].'?mainmenu=tools&product_id='.$_REQUEST['product_id'].'&id_cat='.$id_cat;

    switch($_REQUEST['page']) {
        case 'spare_parts': {//унікальні з/ч
            $Table = '<div id="groupproducts" style="float: left;width: 300px">'.ShowCategories().'</div>';
            $Table .= '<div style="float: left;width: 500px;margin-top: -19px">';
            $Table .= $product_static->ShowSpareParts($_REQUEST['product_id']).'</body></table></div>';
            include($_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/eldy/products/spare_parts.html');
        }break;
        case 'working_units': {//стандартні вироби
            $Table = '<div id="groupproducts" style="float: left;width: 300px">'.ShowCategories().'</div>';
            $Table .= '<div style="float: left;width: 500px;margin-top: -19px">';
            $Table .= $product_static->ShowWorkingUnits($_REQUEST['product_id']).'</body></table></div>';
            include($_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/eldy/products/working_units.html');
        }break;
        case 'tech_parameter': {//Технічні характеристики
            $Table = GetAtributes($_REQUEST['product_id'], $_REQUEST['id_cat']);
            include($_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/eldy/products/addproduct.html');
        }break;
        default:{//1С
            $Table = '';

            include($_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/eldy/products/link_1S.html');
        }break;
    }

exit();
}
global $hookmanager, $menumanager;


//$socstatic=new Societe($db);
$PriceListManager = $langs->trans("PriceListManager");
llxHeader("",$PriceListManager,"");
print_fiche_titre($PriceListManager);
$form = new Form($db);
$Categories = ShowCategories();
$id_cat = $_REQUEST['id_cat'];
if(empty($id_cat))
    $id_cat = ShowCategories(true);
$Products = ShowProducts($id_cat);
$page=$_REQUEST['page'];

ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/products/pricelist_manager.html');
echo ob_get_clean();
exit();

function GetAtributes($product_id, $id_cat){
    global $db;
    $sql='select distinct `oc_product_description`.`product_id`, `oc_product_description`.`name`, `oc_product_attribute`.`attribute_id`, `oc_attribute_description`.language_id, `oc_attribute_description`.`name` as atribute_name, `oc_product_attribute`.`text` from `oc_product_to_category`
        left join `oc_product_description` on `oc_product_description`.`product_id` = `oc_product_to_category`.`product_id`
        left join `oc_product_attribute` on `oc_product_attribute`.`product_id`=`oc_product_description`.`product_id`
        left join `oc_attribute_description` on `oc_attribute_description`.`attribute_id`=`oc_product_attribute`.`attribute_id`
        where `oc_product_to_category`.category_id='.$id_cat.'
        and `oc_product_description`.`product_id`='.$product_id.'
        and `oc_product_description`.language_id = 4
        and `oc_attribute_description`.language_id = `oc_product_description`.language_id
        and length(`oc_product_attribute`.`text`)>0
        and length(`oc_attribute_description`.`name`)>0
        order by `oc_product_description`.`name`';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $dublecate = array();
    while($obj = $db->fetch_object($res)){
        if(!isset($dublecate[$obj->attribute_id]))
            $dublecate[$obj->attribute_id]=1;
        else
            $dublecate[$obj->attribute_id]+=1;
    }
    mysqli_data_seek($res,0);
    $out = '';
    while($obj = $db->fetch_object($res)){
        if($dublecate[$obj->attribute_id] == 1){
            $out .= '<tr>
                <td>'.$obj->atribute_name.'</td>
                <td><input id="'.$obj->attribute_id.'" name="attribute'.$obj->attribute_id.'" value="'.$obj->text.'" maxlength="128" size="60"></td>
            </tr>';
        }
        $dublecate[$obj->attribute_id]-=1;
    }
    return $out;
}
function ShowProducts($id_cat){
    global $db;
    if($id_cat==0){
        $id_cat = ShowCategories(true);
    }
//    var_dump($id_cat);
//    die('test');
    $sql = 'select `oc_product_description`.`product_id`, `oc_product_description`.`name` from `oc_product_to_category`
        left join `oc_product_description` on `oc_product_description`.`product_id` = `oc_product_to_category`.`product_id`
        where `oc_product_to_category`.category_id in (select '.$id_cat.' union select category_id from `oc_category`
				where parent_id = '.$id_cat.')
        and `oc_product_description`.language_id=4
        order by `oc_product_description`.`name`';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $table='';
    $nom = 0;
    while($obj = $db->fetch_object($res)){
        $class=(fmod($nom++,2)==0?"impair":"pair");
        $table .= '<tr id="tr'.$obj->product_id.'" class = "'.$class.' middle_size">
                <td style="width:450px"><a href="' .$_SERVER['PHP_SELF'].'?mainmenu=tools&product_id='.$obj->product_id.'&id_cat='.$id_cat.'">'.$obj->name.'</a></td>
            </tr>';
    }
    return $table;
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
    $id_cat = $_REQUEST['id_selcat'];
    while(count($basic_group)) {
        $catalog_id = $basic_group[0];
        array_shift($basic_group);
        if(isset($sub_category[$catalog_id]) || $categries[$catalog_id][1] == 1) {
            $out .= '<ul id="cat'.$catalog_id.'" '.($categries[$catalog_id][1]!=0?'class="subcatalog"':'').'><a href="' .$_SERVER['PHP_SELF'].'?mainmenu=tools&id_cat='.$catalog_id.'#cat'.$catalog_id.'">' .$categries[$catalog_id][0] . '</a></ul>';
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