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
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
$execption = array('get_choosed_product', 'showorders', 'get_typical_question', 'get_question', 'save_orders', 'del_query', 'showproducts', 'getsavedorder', 'savepreparedraport');

if(isset($_REQUEST['type_action']) && !in_array($_REQUEST['type_action'],$execption) || !isset($_REQUEST['type_action'])) {
    $Orders = $langs->trans('Orders');
    llxHeader("", $Orders, "");
    print_fiche_titre($langs->trans('Orders'));
}
$customername = '';
if(isset($_REQUEST['socid'])&& !empty($_REQUEST['socid'])){
    $sql = 'select nom from `llx_societe` where rowid = '.$_REQUEST['socid'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $customername = $obj->nom;
}


if(isset($_REQUEST['type_action'])){
    switch($_REQUEST['type_action']){
        case 'del_query':{
            $sql = 'update llx_orders set status = -1, id_usr='.$user->id.' where rowid='.$_REQUEST['order_id'];
            $res = $db->query($sql);
//            header("Location: ".$_SERVER["HTTP_REFERER"]);
            exit();
        }break;
        case 'savepreparedraport':{

            if(empty($_REQUEST['rowid'])){
                $sql = "insert into llx_order_prepare(`fk_order`,`fk_product_id`,`proposed`,`Col`,`price_per_unit`,
                        `full_price_per_unit`,`price_per_party`,`full_price_per_party`,`price_per_download`,
                        `price_per_transport`,`full_price_per_transport1`,`price_per_transport2`,`full_price_per_transport2`,
                        `price_per_transport3`,`full_price_per_transport3`,`prep_sale`,`use_owner_transport`,
                        `departure_to_customer`,`service_costs`,`other_gratitude`,`loading_unloading`,`bank_cost`,`other`,
                        `incom_tax`,`VAT`,`price_costs`,`full_price_costs`,`sale_price`,`full_sale_price`,
                        `absolute_diference`,`percent_diference`,`min_advance`,`dead_line_advance`,`dead_line_payment`,
                        `supply`,`note`,`id_usr`) values(
                        ".$_REQUEST['fk_order'].",".$_REQUEST['fk_product_id'].",".($_REQUEST['proposed']=='true'?'1':'0').",".(empty($_REQUEST['Col'])?"null":$_REQUEST['Col']).",".(empty($_REQUEST['price_per_unit'])?"null":$_REQUEST['price_per_unit']).",
                        ".(empty($_REQUEST['full_price_per_unit'])?"null":$_REQUEST['full_price_per_unit']).",
                        ".(empty($_REQUEST['price_per_party'])?"null":$_REQUEST['price_per_party']).",
                        ".(empty($_REQUEST['full_price_per_party'])?"null":$_REQUEST['full_price_per_party']).",
                        ".(empty($_REQUEST['price_per_download'])?"null":$_REQUEST['price_per_download']).",
                        ".(empty($_REQUEST['price_per_transport'])?"null":$_REQUEST['price_per_transport']).",
                        ".(empty($_REQUEST['full_price_per_transport1'])?"null":$_REQUEST['full_price_per_transport1']).",
                        ".(empty($_REQUEST['price_per_transport2'])?"null":$_REQUEST['price_per_transport2']).",
                        ".(empty($_REQUEST['full_price_per_transport2'])?"null":$_REQUEST['full_price_per_transport2']).",
                        ".(empty($_REQUEST['price_per_transport3'])?"null":$_REQUEST['price_per_transport3']).",
                        ".(empty($_REQUEST['full_price_per_transport3'])?"null":$_REQUEST['full_price_per_transport3']).",
                        ".(empty($_REQUEST['prep_sale'])?"null":$_REQUEST['prep_sale']).",
                        ".(empty($_REQUEST['use_owner_transport'])?"null":$_REQUEST['use_owner_transport']).",
                        ".(empty($_REQUEST['departure_to_customer'])?"null":$_REQUEST['departure_to_customer']).",
                        ".(empty($_REQUEST['service_costs'])?"null":$_REQUEST['service_costs']).",
                        ".(empty($_REQUEST['other_gratitude'])?"null":$_REQUEST['other_gratitude']).",
                        ".(empty($_REQUEST['loading_unloading'])?"null":$_REQUEST['loading_unloading']).",
                        ".(empty($_REQUEST['bank_cost'])?"null":$_REQUEST['bank_cost']).",
                        ".(empty($_REQUEST['other'])?"null":$_REQUEST['other']).",
                        ".(empty($_REQUEST['incom_tax'])?"null":$_REQUEST['incom_tax']).",
                        ".(empty($_REQUEST['VAT'])?"null":$_REQUEST['VAT']).",
                        ".(empty($_REQUEST['price_costs'])?"null":$_REQUEST['price_costs']).",
                        ".(empty($_REQUEST['full_price_costs'])?"null":$_REQUEST['full_price_costs']).",
                        ".(empty($_REQUEST['sale_price'])?"null":$_REQUEST['sale_price']).",
                        ".(empty($_REQUEST['full_sale_price'])?"null":$_REQUEST['full_sale_price']).",
                        ".(empty($_REQUEST['absolute_diference'])?"null":$_REQUEST['absolute_diference']).",
                        ".(empty($_REQUEST['percent_diference'])?"null":$_REQUEST['percent_diference']).",
                        ".(empty($_REQUEST['min_advance'])?"null":("'".$_REQUEST['min_advance'])."'").",
                        ".(empty($_REQUEST['dead_line_advance'])?"null":("'".$_REQUEST['dead_line_advance'])."'").",
                        ".(empty($_REQUEST['dead_line_payment'])?"null":("'".$_REQUEST['dead_line_payment'])."'").",
                        ".(empty($_REQUEST['supply'])?"null":("'".$_REQUEST['supply'])."'").",
                        ".(empty($_REQUEST['note'])?"null":("'".$_REQUEST['note'])."'").",
                        ".$user->id.")";
            }else{
                $sql = "update llx_order_prepare set ";
                $sql.= "`proposed`=".($_REQUEST['proposed']=='true'?'1':'0').",";
                $sql.= "`Col`=".(empty($_REQUEST['Col'])?"null":$_REQUEST['Col']).",";
                $sql.= "`price_per_unit`=".(empty($_REQUEST['price_per_unit'])?"null":$_REQUEST['price_per_unit']).", ";
                $sql.= "`full_price_per_unit`=".(empty($_REQUEST['full_price_per_unit'])?"null":$_REQUEST['full_price_per_unit']).",";
                $sql.= "`price_per_party`=".(empty($_REQUEST['price_per_party'])?"null":$_REQUEST['price_per_party']).",";
                $sql.= "`full_price_per_party`=".(empty($_REQUEST['full_price_per_party'])?"null":$_REQUEST['full_price_per_party']).",";
                $sql.= "`price_per_download`=".(empty($_REQUEST['price_per_download'])?"null":$_REQUEST['price_per_download']).",";
                $sql.= "`price_per_transport`=".(empty($_REQUEST['price_per_transport'])?"null":$_REQUEST['price_per_transport']).",";
                $sql.= "`full_price_per_transport1`=".(empty($_REQUEST['full_price_per_transport1'])?"null":$_REQUEST['full_price_per_transport1']).",";
                $sql.= "`price_per_transport2`=".(empty($_REQUEST['price_per_transport2'])?"null":$_REQUEST['price_per_transport2']).",";
                $sql.= "`full_price_per_transport2`=".(empty($_REQUEST['full_price_per_transport2'])?"null":$_REQUEST['full_price_per_transport2']).",";
                $sql.= "`price_per_transport3`=".(empty($_REQUEST['price_per_transport3'])?"null":$_REQUEST['price_per_transport3']).",";
                $sql.= "`full_price_per_transport3`=".(empty($_REQUEST['full_price_per_transport3'])?"null":$_REQUEST['full_price_per_transport3']).",";
                $sql.= "`prep_sale`=".(empty($_REQUEST['prep_sale'])?"null":$_REQUEST['prep_sale']).",";
                $sql.= "`use_owner_transport`=".(empty($_REQUEST['use_owner_transport'])?"null":$_REQUEST['use_owner_transport']).",";
                $sql.= "`departure_to_customer`=".(empty($_REQUEST['departure_to_customer'])?"null":$_REQUEST['departure_to_customer']).",";
                $sql.= "`service_costs`=".(empty($_REQUEST['service_costs'])?"null":$_REQUEST['service_costs']).",";
                $sql.= "`other_gratitude`=".(empty($_REQUEST['other_gratitude'])?"null":$_REQUEST['other_gratitude']).",";
                $sql.= "`loading_unloading`=".(empty($_REQUEST['loading_unloading'])?"null":$_REQUEST['loading_unloading']).",";
                $sql.= "`bank_cost`=".(empty($_REQUEST['bank_cost'])?"null":$_REQUEST['bank_cost']).",";
                $sql.= "`other`=".(empty($_REQUEST['other'])?"null":$_REQUEST['other']).",";
                $sql.= "`incom_tax`=".(empty($_REQUEST['incom_tax'])?"null":$_REQUEST['incom_tax']).",";
                $sql.= "`VAT`=".(empty($_REQUEST['VAT'])?"null":$_REQUEST['VAT']).",";
                $sql.= "`price_costs`=".(empty($_REQUEST['price_costs'])?"null":$_REQUEST['price_costs']).",";
                $sql.= "`full_price_costs`=".(empty($_REQUEST['full_price_costs'])?"null":$_REQUEST['full_price_costs']).",";
                $sql.= "`sale_price`=".(empty($_REQUEST['sale_price'])?"null":$_REQUEST['sale_price']).",";
                $sql.= "`full_sale_price`=".(empty($_REQUEST['full_sale_price'])?"null":$_REQUEST['full_sale_price']).",";
                $sql.= "`absolute_diference`=".(empty($_REQUEST['absolute_diference'])?"null":$_REQUEST['absolute_diference']).",";
                $sql.= "`percent_diference`=".(empty($_REQUEST['percent_diference'])?"null":$_REQUEST['percent_diference']).",";
                $sql.= "`min_advance`=".(empty($_REQUEST['min_advance'])?"null":("'".$_REQUEST['min_advance'])."'").",";
                $sql.= "`dead_line_advance`=".(empty($_REQUEST['dead_line_advance'])?"null":("'".$_REQUEST['dead_line_advance'])."'").",";
                $sql.= "`dead_line_payment`=".(empty($_REQUEST['dead_line_payment'])?"null":("'".$_REQUEST['dead_line_payment'])."'").",";
                $sql.= "`supply`=".(empty($_REQUEST['supply'])?"null":("'".$_REQUEST['supply'])."'").",";
                $sql.= "`note`=".(empty($_REQUEST['note'])?"null":("'".$_REQUEST['note'])."'").",";
                $sql.= "`id_usr`=".$user->id;
                $sql.= " where 1 ";
                $sql.= "and rowid=".$_REQUEST['rowid'];


            }
//            echo '<pre>';
//            var_dump($sql);
//            echo '</pre>';
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            exit();
        }break;
        case 'showproducts':{
            require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
            $product_static = new Product($db);
            if(!isset($_REQUEST['id_cat']))
                $id_cat = $product_static->ShowCategories(true);
            else
                $id_cat = $_REQUEST['id_cat'];
            echo $product_static->ShowProducts($id_cat);
            exit();
        }break;
        case 'with_list':{
            $actionform=ShowPriceList();
        }break;
        case 'without_list':{
//                    echo '<pre>';
//                    var_dump($_REQUEST);
//                    echo '</pre>';
//                    die();
            require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
            global $db;
            $product_static = new Product($db);
            echo '<div class="tabPage" style="width: 1000px">';
            echo '    <div id="groupproducts" style="float: left">';
            $categories = $product_static->ShowCategories(false, true);
            echo $categories;
//            <a href="/dolibarr/htdocs/orders.php?mainmenu=orders&id_cat=446#cat446">
            echo '    </div>';
            echo '    <div id="anketa" style="float: left; margin-left: 15px; width: 680px; height: 100%; background-color: #f5f8f9">';
            echo '    </div>';
            echo '</div>';
//            echo '<pre>';
            if(!isset($_REQUEST['id_cat']))
                $id_cat = $product_static->ShowCategories(true);
            else
                $id_cat = $_REQUEST['id_cat'];
//            var_dump();
//            echo '</pre>';
//            die();
            $queries = ShowQuestion($id_cat, $_REQUEST['page']);
            include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/order_without_list.html';
            exit();
        }break;
        case 'internal':{

        }break;
        case 'prepare_order': {
            require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
            global $db;
            $product_static = new Product($db);
            $products_id = array();
//            var_dump($_REQUEST, $_COOKIE['proposed_id']);
//            die();
//            if (isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id'])){
//                $sql = 'select products_id from `llx_orders` where rowid='.$_REQUEST['order_id'].' limit 1';
//                $res = $db->query($sql);
//                if(!$res)
//                    dol_print_error($db);
//                $obj = $db->fetch_object($res);
//                $val = explode(';', $obj->products_id);
//                for($i=0; $i<count($val); $i++){
//                    $tmp = explode('=', $val[$i]);
//                    $products_id[$tmp[0]]=$tmp[1];
//                }
//                if(count($products_id)>0)
//                    $_COOKIE['proposed_id'] = null;
//            }
//            var_dump(!empty($_COOKIE['proposed_id']));
//            die();
            if(!empty($_COOKIE['proposed_id'])) {
                $id_array = explode(',',$_COOKIE['proposed_id']);
                foreach($id_array as $id) {
                    if(!empty($_COOKIE['pr'.$id]))
                        $products_id[$id]=$_COOKIE['pr'.$id];
//                    var_dump($_COOKIE['pr'.$id]);
                }
//                $products_id = implode(',', $_COOKIE['proposed_id']);
            }
            $order = ShowPrepareOrder($_REQUEST['order_id'], $products_id);
            $question = GetQuestion($_REQUEST['order_id']);
            include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/prepare_order.html';
            exit();
        }break;
        case 'showorders':{
            require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
            global $db;
            $product_static = new Product($db);
            if(isset($_REQUEST['order_id'])&&!empty($_REQUEST['order_id']))
                echo $product_static->ShowOrders($_REQUEST['order_id']);
            else
                echo $product_static->ShowOrders('', $_REQUEST['products_id']);
            exit();

        }break;
        case 'save_orders': {
//            echo '<pre>';
//            var_dump($_REQUEST);
//            echo '</pre>';
//            die();
            if(isset($_REQUEST['socid'])&& !empty($_REQUEST['socid']))
                $socid = $_REQUEST['socid'];
            else
                $socid = 'null';
            global $db;
            $order_id = 0;
            if (isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id'])){
                $order_id = $_REQUEST['order_id'];
            }elseif(!isset($_REQUEST['order_id'])||empty($_REQUEST['order_id'])){
                $sql = 'insert into llx_orders(socid,status,dtCreated,id_usr) values('.$socid.', 0, Now(), '.$user->id.')';
                $res = $db->query($sql);
                if (!$res)
                    dol_print_error($db);
                $sql = 'select rowid from llx_orders where id_usr = ' . $user->id . ' and status = 0 limit 1';
                $res = $db->query($sql);
                if (!$res)
                    dol_print_error($db);
                $obj = $db->fetch_object($res);
                $order_id = $obj->rowid;

            }
//            echo '<pre>';
//            var_dump($order_id);
//            echo '</pre>';
//            die();
            if($order_id != 0 && isset($_REQUEST['products'])){
                $products = $_REQUEST['products'];
                $products = str_replace('"','',$products);
                $products = str_replace(',',';',$products);
                $products = str_replace(':','=',$products);
                $products = str_replace('{','',$products);
                $products = str_replace('}','',$products);
                $sql = "update llx_orders set products_id = '".$products."', id_usr = ".$user->id." where rowid=".$order_id;
                $res = $db->query($sql);
//                echo '<pre>';
//                var_dump($order_id, $sql);
//                echo '</pre>';
//                die();
            }

            $inserted_questions = array();
            $sql = 'select rowid, query_id from `llx_orders_queries` where order_id = '.$order_id;
            $res = $db->query($sql);

            if(!$res)
                dol_print_error($db);
            if($db->num_rows($res)>0)
                while($obj = $db->fetch_object($res)){
                    $inserted_questions[$obj->query_id] = $obj->rowid;
                }
            //Save categories question
            if(isset($_REQUEST['answer']) && !empty($_REQUEST['answer'])) {
                $json = $_REQUEST['answer'];
                $answer = array();
                eval("\$answer = array".$json.';');
//                echo '<pre>';
                foreach(array_keys($answer) as $key) {
                    if(isset($inserted_questions[$obj->rowid])){
                        $sql="update llx_orders_queries set answer = '".trim($db->escape($answer[$key]))."', id_usr=".$user->id." where rowid=".$inserted_questions[$obj->rowid];
                    }else{
                        $sql="insert into llx_orders_queries(order_id,query_id,answer,id_usr)
                            values(".$order_id.", ".$key.", '".trim($db->escape($answer[$key]))."', ".$user->id.")";
                    }
                    $res = $db->query($sql);
                    if(!$res)
                        dol_print_error($db);
//                    die($sql);
                }
//                echo '</pre>';
//                die();
            }
//            die('test');
            //Save typical question
            $sql = 'select rowid from `llx_c_category_product_question` where category_id is null and active = 1';
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
//            var_dump($res);
//            die();
            while($obj = $db->fetch_object($res)){
                if(isset($_REQUEST['q'.$obj->rowid])){
                    if(isset($inserted_questions[$obj->rowid])){
                        $sql="update llx_orders_queries set answer = '".trim($db->escape($_REQUEST['q'.$obj->rowid]))."', id_usr=".$user->id." where rowid=".$inserted_questions[$obj->rowid];
                    }else{
                        $sql="insert into llx_orders_queries(order_id,query_id,answer,id_usr)
                            values(".$order_id.", ".$obj->rowid.", '".trim($db->escape($_REQUEST['q'.$obj->rowid]))."', ".$user->id.")";

                    }
//                    echo $sql.'</br>';
                    $res_answer = $db->query($sql);
                    if(!$res_answer) {
                        dol_print_error($db);
                        die($sql);
                    }
                }
            }
//            die('test');
            SendTaskForPurchase($order_id);
            $sql = 'update llx_orders set `status` = 1 where rowid='.$order_id;
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
//            echo '<pre>';
//            var_dump($_REQUEST);
//            echo '</pre>';
//            die();
            header("Location: ".$_SERVER["HTTP_REFERER"]);
            exit();
        }break;
        case 'getsavedorder':{
//            echo '<pre>';
//            var_dump($_REQUEST);
//            echo '</pre>';
//            die();
            global $db;
            $order = array();
            $sql = 'select products_id from `llx_orders` where rowid = '.$_REQUEST['order_id'];
//            die($sql);
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            if($db->num_rows($res)>0) {
                $obj = $db->fetch_object($res);
                $order['products_id'] = $obj->products_id;
            }
            $sql = 'select `llx_orders_queries`.`query_id`, `llx_orders_queries`.`answer` from `llx_orders_queries`
                inner join `llx_c_category_product_question` on `llx_c_category_product_question`.`rowid` = `llx_orders_queries`.`query_id`
                where order_id = '.$_REQUEST['order_id'].'
                and `llx_c_category_product_question`.`category_id` is not null';
            $res = $db->query($sql);
//            die($sql);
            if(!$res)
                dol_print_error($db);
            if($db->num_rows($res)>0) {
                $queries = array();
                while($obj = $db->fetch_object($res)) {
                    $queries[$obj->query_id]=$obj->answer;
                }
                $order['queries']=$queries;
            }
            $json =  json_encode($order);
            echo $json;
            exit();
        }break;
        case 'get_question':{
            $questions = ShowQuestion($_REQUEST['id_cat'], $_REQUEST['page'], $_REQUEST['answer_id']);
            echo $questions;
            exit();
        }break;
        case 'get_typical_question':{
            global $db;
            $sql = 'select rowid, question from `llx_c_category_product_question`
              where category_id is null and active = 1';
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            if($db->num_rows($res)){
                $out = '<tbody>';
                $num = 0;
                while($obj = $db->fetch_object($res)){
                    $class = (fmod($num++, 2)==0?'impair':'pair');
                    $out .= '<tr>
                        <td>
                            <b class="middle_size">'.trim($obj->question).'</b></br>
                            <textarea id="q'.$obj->rowid.'" class="answers" name="q'.$obj->rowid.'" max_length = 250 style="width:100%"></textarea>
                        </td>
                    </tr>';
                }
                $out .='</tbody>';
            }
            echo $out;
            exit();
        }
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

if(!isset($_REQUEST['type_action']))
    $orders = ShowOrders();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/orders.html';
//llxFooter();
exit();
function ShowQuestion($id_cat, $page=1, $answer_id=''){

    if(empty($page))
        $page = 1;
    global $db, $user;
    if(empty($answer_id)) {
        $sql = 'select llx_c_category_product_question.rowid, llx_c_category_product_question.category_id, question, page from `llx_c_category_product_question`
        where llx_c_category_product_question.category_id = ' . $id_cat . '
        and llx_c_category_product_question.active = 1
        and page = ' . $page;
//    var_dump($sql);
//    die();
        $res_queries = $db->query($sql);
        if (!$res_queries)
            dol_print_error($db);
        $sql = 'select `llx_orders_queries`.`query_id`, `llx_c_category_product_question`.`category_id`,`oc_category_description`.`name`, `llx_orders_queries`.`answer` from `llx_orders`
        left join `llx_orders_queries` on `llx_orders_queries`.`order_id` = `llx_orders`.`rowid`
        inner join `llx_c_category_product_question` on  `llx_orders_queries`.`query_id` =`llx_c_category_product_question` .`rowid`
        left join `oc_category_description` on llx_c_category_product_question.category_id = `oc_category_description`.category_id
        where `llx_orders`.id_usr = ' . $user->id . '
        and `llx_orders`.`status` = 0
        and `llx_c_category_product_question`.`category_id` is not null
        and `llx_c_category_product_question`.`active` = 1
        and `oc_category_description`.`language_id`=4';
    }else{
        $sql = "select llx_c_category_product_question.rowid as rowid, `oc_category_description`.`name`, llx_c_category_product_question.rowid query_id, llx_c_category_product_question.category_id, question, '' as answer  from `llx_c_category_product_question`
        left join `oc_category_description` on llx_c_category_product_question.category_id = `oc_category_description`.category_id
        where llx_c_category_product_question.rowid in (" . $answer_id . ")
        and `oc_category_description`.`language_id`=4";
//        return $answer_id;
    }
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
//    die($sql);
    $res_answer = $db->query($sql);
    if (!$res_answer)
        dol_print_error($db);
    $answer = array();
    while ($obj = $db->fetch_object($res_answer)) {
        $answer[$obj->query_id] = trim($obj->answer);
    }
//    echo '<pre>';
//    var_dump($answer);
//    echo '</pre>';
//    die();
    $category_color = array('#e2ffe2','#dff1ff', '#e2ffe2', '#BBDDFF');
    $out = '';
    if(empty($answer_id))
        $out .= '<table class="WidthScroll" cellspacing="1"> ';
    else{
        mysqli_data_seek($res_answer, 0);
    }
    $out .= '<tbody id="queries">';
    $num = 1;
    $prev_category = -1;
    $num_color = -1;
    while($obj = $db->fetch_object(empty($answer_id)?$res_queries:$res_answer)){
        if($prev_category != $obj->category_id){
            $prev_category=$obj->category_id;
            $num_color++;
            if($num_color>3)$num_color=0;
            $out .= '<tr title="'.$obj->name.'">';
            $out .= '<td colspan="2"  style="background-color: '.$category_color[$num_color].';font-size:14px" ><b>'.$obj->name.'</b></td>';
            $out .= '</tr>';
        }
//        $class = (fmod($num++, 2)==0?'impair':'pair');
        $out .= '<tr title="'.$obj->name.'">';
        $out .= '<td id="q'.$obj->rowid.'" style="background-color: '.$category_color[$num_color].'" >'.$obj->question.'</td>';
        $out .= '</tr>';
//        $class = (fmod($num++, 2)==0?'impair':'pair');
        $out .= '<tr id="a'.$obj->rowid.'" title="'.$obj->name.'" >';
        $out .= '<td  colspan="2" style="background-color: '.$category_color[$num_color].'"><textarea id="answer'.$obj->rowid.'" class="answer" style="width: 90%">'.(isset($answer[$obj->rowid])?$answer[$obj->rowid]:'').'</textarea></td>';
        $out .= '</tr>';
    }
    $out.= '</tbody>';
    if(empty($answer_id))
        $out .= '</table>';
    return $out;
}
function ShowOrders(){
    global $db, $user;
    $sql = 'select `llx_orders`.`rowid`, `llx_orders`.`dtCreated`, case when `llx_societe`.rowid is null then `llx_user`.lastname else `llx_societe`.`nom` end customer,
        max(`llx_actioncomm`.`datep`) as date_exec, `llx_orders`.`status`
        from `llx_orders`
        left join `llx_societe`on `llx_societe`.`rowid`=`llx_orders`.`socid`
        left join `llx_actioncomm` on `llx_actioncomm`.`fk_order_id`=`llx_orders`.`rowid`
        left join `llx_user` on `llx_user`.`rowid`=`llx_orders`.`id_usr`';
    if(isset($_REQUEST['socid'])&& !empty($_REQUEST['socid'])){
        $sql.= ' where `llx_societe`.`rowid`='.$_REQUEST['socid'];
    }else{
        $sql.= ' where `llx_orders`.`id_usr` = '.$user->id.'
            and `llx_orders`.`status` in (0,1,2)';
    }
    $sql.= ' group by `llx_orders`.`rowid`, `llx_orders`.`dtCreated`, `llx_societe`.`nom`
        order by dtCreated desc';
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $sql = 'select distinct `llx_orders`.`rowid`, `llx_orders`.id_usr, `llx_user`.`lastname`
        from `llx_orders`
        left join `llx_actioncomm` on `llx_actioncomm`.`fk_order_id`=`llx_orders`.`rowid`
        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm`=`llx_actioncomm`.`id`
        left join `llx_user` on `llx_user`.rowid = `llx_actioncomm_resources`.fk_element
        where `llx_orders`.`id_usr` = '.$user->id.' and `llx_orders`.`status` in (0,1,2)
        and `llx_user`.`lastname` is not null';
    $res_purchase = $db->query($sql);
    if(!$res_purchase)
        dol_print_error($db);
//    echo '<pre>';
//    var_dump($db->num_rows($res_purchase));
//    echo '</pre>';
//    die();
    if($db->num_rows($res_purchase) == 0 && $user->respon_alias == 'purchase') {
        $sql = 'select distinct `llx_orders`.`rowid`, `llx_user`.`lastname`
        from `llx_orders`
        left join `llx_actioncomm` on `llx_actioncomm`.`fk_order_id`=`llx_orders`.`rowid`
        left join `llx_user` on `llx_user`.rowid = `llx_orders`.`id_usr`
        where `llx_orders`.`id_usr` = ' . $user->id . '
        and `llx_orders`.`status` in (0,1,2)';
        $res_purchase = $db->query($sql);
        if(!$res_purchase)
            dol_print_error($db);
        }

    $purchase = array();
    while($obj = $db->fetch_object($res_purchase)){
        if(!isset($purchase[$obj->rowid])) {
            $purchase[$obj->rowid] = $obj->lastname;
        }else {
            $purchase[$obj->rowid] .= ', ' . $obj->lastname;
        }
    }

    $out = '<tbody>';
    $nom = 0;
    while($obj = $db->fetch_object($res)){
        $class=(fmod($nom++,2)==0?"impair":"pair");
        $out .= '<tr id="'.$obj->rowid.'" class = "'.$class.'">';
        $datecreate = new DateTime($obj->dtCreated);
        $out .= '<td class="small_size" style="text-align: center">'.$datecreate->format('d.m').'</br>'.$datecreate->format('H:i').'</td>';
        $out .= '<td class="small_size">'.$obj->customer.'</td>';
        $date = new DateTime($obj->date_exec);
        $out .= '<td class="small_size" style="text-align: center">'.$date->format('d.m').'</br>'.$date->format('H:i').'</td>';
        if(!isset($purchase[$obj->rowid]))
            $out .= '<td class="small_size" style="text-align: center"></td>';
        else
            $out .= '<td class="small_size" style="text-align: center">'.$purchase[$obj->rowid].'</td>';
        $status = '';
        switch($obj->status){
            case 0:{
                $status = 'Не сформована';
            }break;
            case 1:{
                $status = 'Відправлена';
            }break;
            case 2:{
                $status = 'В роботі';
            }break;
            case 3:{
                $status = 'Оброблено';
            }break;
        }
        $out .= '<td class="small_size">'.$status.'</td>';
        $out .= '<td class="small_size"><img src="theme/eldy/img/edit.png" onclick="showorders('.$obj->rowid.');" title="Редагувати" style="cursor: pointer">&nbsp;
                                        <img src="theme/eldy/img/delete.png" onclick="deleteorder('.$obj->rowid.');" title="Видалити" style="cursor: pointer">&nbsp;
                                        <img src="theme/eldy/img/object_logistic.png" title="Відправити у відділ логістики" style="cursor: pointer">&nbsp;</td>';
        $out .= '</tr>';
    }
    $out .= '</tbody>';
    return $out;
}
function ShowPrepareOrder($orders_id = 0, $products = array())
{

    global $db, $user;
    $purchase = false;
//    if(count($products) == 0) {
        $sql = 'select products_id from llx_orders ';
        if (empty($orders_id))
            $sql .= 'where status = 0 and id_usr = ' . $user->id;
        else
            $sql .= 'where rowid = ' . $orders_id;
        $sql .= ' limit 1';
        $res = $db->query($sql);
        $out = '';
        if ($db->num_rows($res) > 0) {
            $obj = $db->fetch_object($res);
            $productlist = explode(';', $obj->products_id);
        }


        foreach ($productlist as $product => $value) {
            if (!empty($value)) {
                $item = explode('=', $value);
                $products[-$item[0]] = $item[1];
            }
        }
//    }
//    var_dump(str_replace('-','', implode(',', array_keys($products))));
//    die();
        $purchase = true;
    if(count($products)>0) {
        $sql = 'select product_id, price from oc_product where product_id in (' . str_replace('-','', implode(',', array_keys($products))) . ')';
        $res = $db->query($sql);
        $price = array();
        if ($db->num_rows($res) > 0)
            while ($obj = $db->fetch_object($res)) {
                $price[$obj->product_id] = round($obj->price, 2);
            }
    }
//var_dump($products);
    $prepared_raport = array();
    if($orders_id != 0){//Load prepared rapor
        $sql = 'select rowid,fk_order,fk_product_id,proposed,Col,price_per_unit,full_price_per_unit,price_per_party,
              full_price_per_party,price_per_download,price_per_transport,full_price_per_transport1,price_per_transport2,
              full_price_per_transport2,price_per_transport3,full_price_per_transport3,prep_sale,use_owner_transport,
              departure_to_customer,service_costs,other_gratitude,loading_unloading,bank_cost,other,incom_tax,VAT,price_costs,
              full_price_costs,sale_price,full_sale_price,absolute_diference,percent_diference,min_advance,
              dead_line_advance,dead_line_payment,supply,note
            from llx_order_prepare
            where fk_order ='.$orders_id;
        $res_raport = $db->query($sql);
        if(!$res_raport)
            dol_print_error($db);
        while($item = $db->fetch_object($res_raport)){
            $prepared_raport[$item->fk_product_id] = (array)$item;
        }

    }
//            echo '<pre>';
//            var_dump($prepared_raport);
//            echo '</pre>';
//            die();
    $out .= '<tbody>';
    $product_class = new Product($db);
    $result_table = $product_class->ShowProducts(0, str_replace('-','', implode(',', array_keys($products))), 'name');
    $pos = 0;

//'.($purchase == true?'class="proposed"':'').'
    while(gettype(strpos($result_table, '<tr id="tr', $pos)) == 'integer') {
        $pos = strpos($result_table, '<tr id="tr', $pos);
        $product_id = substr($result_table, $pos+10, strpos($result_table, '"', $pos+10)-($pos+10));
        $result_table = substr($result_table, 0, strpos($result_table, '</tr>', $pos+10)).
                '<td '.(in_array($product_id, array_keys($products))?'proposed="1"':'').' id="art'.$product_id.'" '.(isset($prepared_raport[$product_id])?('rowid="'.$prepared_raport[$product_id]['rowid'].'"'):'').'></td>
                 <td id="Col'.$product_id.'" style="width:50px; text-align: center">'.$products[in_array($product_id, array_keys($products))?$product_id:-$product_id].'</td>
                 <td id="Ed'.$product_id.'"></td>
                 <td class="basic_part autocalc" id="price_per_unit'.$product_id.'">'.$prepared_raport[$product_id]['price_per_unit'].'</td>
                 <td class="basic_part input" id="full_price_per_unit'.$product_id.'">'.$prepared_raport[$product_id]['full_price_per_unit'].'</td>
                 <td class="basic_part autocalc" id="price_per_party'.$product_id.'">'.$prepared_raport[$product_id]['price_per_party'].'</td>
                 <td class="basic_part autocalc" id="full_price_per_party'.$product_id.'">'.$prepared_raport[$product_id]['full_price_per_party'].'</td>
                 <td class="basic_part input" id="price_per_download'.$product_id.'">'.$prepared_raport[$product_id]['price_per_download'].'</td>
                 <td class="basic_part autocalc" id="price_per_transport1'.$product_id.'">'.$prepared_raport[$product_id]['price_per_transport1'].'</td>
                 <td class="basic_part input" id="full_price_per_transport1'.$product_id.'">'.$prepared_raport[$product_id]['full_price_per_transport1'].'</td>
                 <td class="basic_part autocalc" id="price_per_transport2'.$product_id.'">'.$prepared_raport[$product_id]['price_per_transport2'].'</td>
                 <td class="basic_part input" id="full_price_per_transport2'.$product_id.'">'.$prepared_raport[$product_id]['full_price_per_transport2'].'</td>
                 <td class="basic_part autocalc" id="price_per_transport3'.$product_id.'">'.$prepared_raport[$product_id]['price_per_transport3'].'</td>
                 <td class="basic_part input" id="full_price_per_transport3'.$product_id.'">'.$prepared_raport[$product_id]['full_price_per_transport3'].'</td>
                 <td class="basic_part input" id="prep_sale'.$product_id.'">'.$prepared_raport[$product_id]['prep_sale'].'</td>
                 <td class="basic_part input" id="use_owner_transport'.$product_id.'">'.$prepared_raport[$product_id]['use_owner_transport'].'</td>
                 <td class="addition_part input" id="departure_to_customer'.$product_id.'">'.$prepared_raport[$product_id]['departure_to_customer'].'</td>
                 <td class="addition_part input" id="service_costs'.$product_id.'">'.$prepared_raport[$product_id]['service_costs'].'</td>
                 <td class="addition_part input" id="other_gratitude'.$product_id.'">'.$prepared_raport[$product_id]['other_gratitude'].'</td>
                 <td class="addition_part input" id="loading_unloading'.$product_id.'">'.$prepared_raport[$product_id]['loading_unloading'].'</td>
                 <td class="addition_part input" id="bank_cost'.$product_id.'">'.$prepared_raport[$product_id]['bank_cost'].'</td>
                 <td class="addition_part input" id="other'.$product_id.'">'.$prepared_raport[$product_id]['other'].'</td>
                 <td class="addition_part autocalc" id="incom_tax'.$product_id.'">'.$prepared_raport[$product_id]['incom_tax'].'</td>
                 <td class="addition_part autocalc" id="VAT'.$product_id.'">'.$prepared_raport[$product_id]['VAT'].'</td>
                 <td class="addition_part" id="price_costs'.$product_id.'">'.$prepared_raport[$product_id]['price_costs'].'</td>
                 <td class="addition_part autocalc" id="full_price_costs'.$product_id.'">'.$prepared_raport[$product_id]['full_price_costs'].'</td>
                 <td class="result_part autocalc" id="sale_price'.$product_id.'">'.$prepared_raport[$product_id]['sale_price'].'</td>
                 <td class="result_part input" id="full_sale_price'.$product_id.'">'.(isset($prepared_raport[$product_id])?$prepared_raport[$product_id]['full_sale_price']:($price[$product_id]*$products[in_array($product_id, array_keys($products))?$product_id:-$product_id])).'</td>
                 <td class="result_part autocalc" id="absolute_diference'.$product_id.'">'.$prepared_raport[$product_id]['absolute_diference'].'</td>
                 <td class="result_part autocalc" id="percent_diference'.$product_id.'">'.$prepared_raport[$product_id]['percent_diference'].'</td>
                 <td class="features_part" id="min_advance'.$product_id.'">'.$prepared_raport[$product_id]['min_advance'].'</td>
                 <td class="features_part" id="dead_line_advance'.$product_id.'">'.$prepared_raport[$product_id]['dead_line_advance'].'</td>
                 <td class="features_part" id="dead_line_payment'.$product_id.'">'.$prepared_raport[$product_id]['dead_line_payment'].'</td>
                 <td class="features_part" id="supply'.$product_id.'">'.$prepared_raport[$product_id]['supply'].'</td>
                 <td class="features_part" id="note'.$product_id.'">'.$prepared_raport[$product_id]['note'].'</td>
                '.substr($result_table, strpos($result_table, '</tr>', $pos+10));
        $pos++;
    }
    $out .= $result_table;
    $out .= '</tbody>';
    return $out;
}
function GetQuestion($order_id){
    global $db;
    $sql = "select * from (select oc_category_description.name, `llx_c_category_product_question`.category_id, `llx_c_category_product_question`.question, `llx_orders_queries`.`answer`
    from `llx_orders_queries`
    left join `llx_c_category_product_question` on `llx_c_category_product_question`.`rowid`=`llx_orders_queries`.`query_id`
    left join oc_category_description on oc_category_description.category_id = llx_c_category_product_question.category_id
    where `llx_orders_queries`.`order_id` = ".$order_id."
    and oc_category_description.`language_id` = 4
    union
    select '', `llx_c_category_product_question`.category_id, `llx_c_category_product_question`.question, `llx_orders_queries`.`answer`
    from `llx_orders_queries`
    left join `llx_c_category_product_question` on `llx_c_category_product_question`.`rowid`=`llx_orders_queries`.`query_id`
    where `llx_orders_queries`.`order_id` = ".$order_id."
    and llx_c_category_product_question.category_id is null
    and length(`llx_orders_queries`.`answer`)>0) report
    order by report.category_id desc ";
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '';

    $category_color = array('#e2ffe2','#dff1ff', '#e2ffe2', '#BBDDFF');

    $num = 1;
    $prev_category = -1;
    $num_color = -1;
    while($obj = $db->fetch_object($res)){
        if(!empty($obj->category_id) && $prev_category != $obj->category_id){
            $prev_category = $obj->category_id;
            $num_color++;
            $out .= '<tr title="'.$obj->name.'"><td '.(!empty($obj->category_id)?'style="background-color: '.$category_color[$num_color]:'').'; width:450px; font-size=14px; border-bottom: 2px solid maroon;"><b>'.$obj->name.'</b></td></tr>';
        }
        $out .= '<tr title="'.$obj->name.'"><td '.(!empty($obj->category_id)?'style="background-color: '.$category_color[$num_color]:'').'; width:450px"><b>'.$obj->question.'</b></td></tr>';
        $out .= '<tr title="'.$obj->name.'"><td '.(!empty($obj->category_id)?'style="background-color: '.$category_color[$num_color]:'').'; width:450px">'.$obj->answer.'</td></tr>';
    }
//    $out = '</tbody></table>';
//var_dump($out);
//    die();
    return $out;
}
function ShowPriceList(){
    require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
    global $db;
    $product_static = new Product($db);

    return $product_static->ShowPriceList();
}
function SendTaskForPurchase($order_id)
{
    global $db, $user;
    $sql = 'select products_id from `llx_orders` where rowid = ' . $order_id;
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $productlist = explode(';', $obj->products_id);
    $products = array();
    if (count($productlist) > 0) {
        foreach ($productlist as $item) {
            $itemarray = explode('=', $item);
            if (count($itemarray) == 2) {
                $products[$itemarray[0]] = $itemarray[1];
            }
        }
    }
    $sql = 'select distinct fk_user from `llx_user_lineactive`
        where fk_lineactive in
            (select distinct category_id from `oc_product_to_category`
            where product_id in ('.(count($products)>0?implode(',', array_keys($products)):0).'))
        or fk_lineactive in
            (select distinct `llx_c_category_product_question`.`category_id` from llx_orders_queries
            inner join `llx_c_category_product_question` on `llx_c_category_product_question`.`rowid` = llx_orders_queries.`query_id`
            where order_id = '.$order_id.'
            and `llx_c_category_product_question`.`category_id` is not null)
        and active = 1';
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
    while($obj = $db->fetch_object($res)){
        $id_usr = $obj->fk_user;
//        var_dump($user->id, $id_usr);
        //http://"+location.hostname+"/dolibarr/htdocs/comm/action/card.php?action=get_freetime&date="+$("#apyear").val()+"-"+$("#apmonth").val()+"-"+$("#apday").val()+"&id_usr="+$("#id_usr").val()+"&minute="+minute;
        $action = new ActionComm($db);
        $exec_minuted = $action->GetExecTime('AC_CURRENT');
        $freetime = $action->GetFirstFreeTime(date('Y-m-d'), $id_usr, $exec_minuted);
        $date = new DateTime($freetime);
        $action->datep = mktime($date->format('h'),$date->format('i'),$date->format('s'),$date->format('m'),$date->format('d'),$date->format('Y'));
        $action->datef = $action->datep + $exec_minuted*60;
        $action->type_code = 'AC_CURRENT';
        $action->order_id = $order_id;
        $action->label = "Опрацювати заявку";
        $action->period = 0;
        $action->percentage = -1;
        $action->priority = 0;
        $action->note = 'Опрацювати заявку';
        $action->userassigned[] = array("id"=>$user->id, "transparency"=>1);
        $action->userassigned[] = array("id"=>$id_usr, "transparency"=>1);
        $action->userownerid = 1;
        $action->fk_element = "";
        $action->elementtype = "";
        $action->add($user);
//        echo '<pre>';
//        var_dump($action);
//        echo '</pre>';

    }
}
