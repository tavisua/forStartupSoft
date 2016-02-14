<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 09.12.2015
 * Time: 9:59
 */

require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

//$res = shell_exec('adb shell am start -a android.intent.action.CALL -d tel:+380505223977');
//var_dump($res);
//die('ответ');
$execption = array('get_choosed_product', 'showorders');

if(isset($_REQUEST['type_action']) && !in_array($_REQUEST['type_action'],$execption) || !isset($_REQUEST['type_action'])) {
    $Orders = $langs->trans('Orders');
    llxHeader("", $Orders, "");
    print_fiche_titre($langs->trans('Orders'));
}


if(isset($_REQUEST['type_action'])){
    switch($_REQUEST['type_action']){
        case 'with_list':{
            $actionform=ShowPriceList();
        }break;
        case 'without_list':{

        }break;
        case 'internal':{

        }break;
        case 'showorders':{
            require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
            global $db;
            $product_static = new Product($db);
            echo $product_static->ShowOrders();
            exit();
        }break;
        case 'get_choosed_product':{
            global $db;
            $sql = 'select products_id from llx_orders where id_usr='.$user->id.' and status = 0 limit 1';
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            if(!$db->num_rows($res))
                echo 0;
            else{
                $obj = $db->fetch_object($res);
                echo $obj->products_id;
            }
            exit();
        }break;
        case 'choose_product':{
            global $db;
            $sql = 'select rowid from llx_orders where id_usr='.$user->id.' and status = 0 limit 1';
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            if($db->num_rows($res) == 0){
                $sql = 'insert into llx_orders(socid,products_id,answer,status,dtCreated,id_usr)
                 values('.(!isset($_REQUEST['socid'])||empty($_REQUEST['socid'])?'null':$_REQUEST['socid']).', "'.$_REQUEST['product_id'].'='.$_REQUEST['count'].'", null, 0, Now(), '.$user->id.')
                ';
                $res = $db->query($sql);
                if(!$res)
                    dol_print_error($db);
            }else{
                $obj = $db->fetch_object($res);
                $sql = 'select rowid, products_id from llx_orders where rowid='.$obj->rowid;
                $res = $db->query($sql);
                if(!$res)
                    dol_print_error($db);
                $obj = $db->fetch_object($res);
                $choosed_products = $obj->products_id;
                //9990=3;9991=4;
//                var_dump(preg_match('/'.$_REQUEST['product_id'].'=[0-9]/', $choosed_products));
//                die($choosed_products);
                if(preg_match('/'.$_REQUEST['product_id'].'=[0-9]/', $choosed_products)){
                    $choosed_products = preg_replace('/'.$_REQUEST['product_id'].'=[0-9]/', $_REQUEST['product_id'].'='.$_REQUEST['count'], $choosed_products);
                }else{
                    if(substr($choosed_products, strlen($choosed_products)-1)!=';')
                        $choosed_products.=';';
                    $choosed_products .= $_REQUEST['product_id'].'='.$_REQUEST['count'].';';
                }
                $sql = 'update llx_orders set products_id = "'.$choosed_products.'", id_usr='.$user->id.' where rowid='.$obj->rowid;
//                die($sql);
                $res = $db->query($sql);
                if(!$res)
                    dol_print_error($db);
            }
            echo 'choose product success';
            exit();
        }break;
    }
}



include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/orders.html';
llxFooter();
exit();

function ShowPriceList(){
    require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
    global $db;
    $product_static = new Product($db);
    return $product_static->ShowPriceList();
}
