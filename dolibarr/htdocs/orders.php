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
$execption = array('get_choosed_product', 'showorders', 'get_typical_question', 'get_question');

if(isset($_REQUEST['type_action']) && !in_array($_REQUEST['type_action'],$execption) || !isset($_REQUEST['type_action'])) {
    $Orders = $langs->trans('Orders');
    llxHeader("", $Orders, "");
    print_fiche_titre($langs->trans('Orders'));
}


if(isset($_REQUEST['type_action'])){
    switch($_REQUEST['type_action']){
        case 'clone_question':{
            global $db;
            $sql = 'select distinct `oc_category`.`parent_id` from `llx_c_category_product_question`
              left join `oc_category` on `oc_category`.`category_id`=`llx_c_category_product_question`.category_id';
            $res_parent = $db->query($sql);
            if(!$res_parent)
                dol_print_error($db);
            $num = 0;
            while($parent = $db->fetch_object($res_parent)){
                $sql = 'select `oc_category`.`category_id` from `oc_category` where `oc_category`.`parent_id` = '.$parent->parent_id;
                $res_categoies = $db->query($sql);
                if(!$res_parent)
                    dol_print_error($db);
                $categories = array();
                while($category = $db->fetch_object($res_categoies)){
                    $categories[]=$category->category_id;
                }
                $questions = array();
                $sql = 'select llx_c_category_product_question.category_id, question, page from `llx_c_category_product_question`
                    inner join `oc_category` on `oc_category`.`category_id` = `llx_c_category_product_question`.category_id
                    where `oc_category`.`parent_id` = '.$parent->parent_id.'
                    and llx_c_category_product_question.active = 1;';
                $res_queries = $db->query($sql);
                if(!$res_queries)
                    dol_print_error($db);
                $category_question = array();
                while($question = $db->fetch_object($res_queries)){
                    $category_question[]=$question->category_id.'|&|'.$question->question.'|&|'.$question->page;

                }
                foreach($categories as $category_id) {
                    foreach ($category_question as $question) {
                        if($category_id.'|&|'.substr($question, strpos($question, '|&|') + 3) != $question) {
                            echo '<b>'.$category_id.'|&|'.substr($question, strpos($question, '|&|') + 3) .' '. $question . '</b></br>';
                            $sql = "select rowid from llx_c_category_product_question where category_id=".$category_id."
                                and question = '".$db->escape(substr($question, strpos($question, '|&|') + 3, strrpos($question, '|&|')-(strpos($question, '|&|') + 3))) ."' limit 1";
                            $insert = $db->query($sql);
                            if($db->num_rows($insert)>0)
                                echo 'введено</br>';
                            else {
                                $sql = "insert into llx_c_category_product_question(category_id,question,page,active,id_usr)
                              values(" . $category_id . ", '" . $db->escape(substr($question, strpos($question, '|&|') + 3, strrpos($question, '|&|') - (strpos($question, '|&|') + 3))) . "', " .
                                    substr($question, strlen($question) - 1) . ", 1, 1)";
                                echo $num++ . ' ' . $sql . '</br>';
                                $insert = $db->query($sql);
                                if(!$insert)
                                    dol_print_error($db);
                            }
                        }
//                    echo '<pre>';
//                    var_dump($questions);
//                    echo '</pre>';
//                    die();
                    }
                }
            }
            exit();
        }break;
        case 'with_list':{
            $actionform=ShowPriceList();
        }break;
        case 'without_list':{
            require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
            global $db;
            $product_static = new Product($db);
            echo '<div class="tabPage" style="width: 1000px">';
            echo '    <div id="groupproducts" style="float: left">';
            $categories = $product_static->ShowCategories();
            echo $categories;
//            <a href="/dolibarr/htdocs/orders.php?mainmenu=orders&id_cat=446#cat446">
            echo '    </div>';
            echo '    <div id="anketa" style="float: left; margin-left: 15px; width: 680px; height: 100%; background-color: #f5f8f9">';
            echo '    </div>';
            echo '</div>';
            include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/order_without_list.html';
            exit();
        }break;
        case 'internal':{

        }break;
        case 'prepare_order':{
            require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
            global $db;
            $product_static = new Product($db);
            $order = ShowPrepareOrder($_REQUEST['order_id']);
            $question = GetQuestion($_REQUEST['order_id']);
            include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/prepare_order.html';
            exit();
        }break;
        case 'showorders':{
            require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
            global $db;
            $product_static = new Product($db);
            echo $product_static->ShowOrders();
            exit();
        }break;
        case 'save_orders':{
            global $db;
            $order_id=0;
            if(isset($_REQUEST['order_id'])&&empty($_REQUEST['order_id'])){
                $sql = 'select rowid from llx_orders where id_usr = '.$user->id.' and status = 0 limit 1';
                $res = $db->query($sql);
                if(!$res)
                    dol_print_error($db);
                $obj = $db->fetch_object($res);
                $order_id = $obj->rowid;
            }elseif(isset($_REQUEST['order_id'])&&!empty($_REQUEST['order_id']))
                $order_id = $_REQUEST['order_id'];
//            var_dump($order_id);
//            die();
            $inserted_questions = array();
            $sql = 'select rowid, query_id from `llx_orders_queries` where order_id = '.$order_id;
            $res = $db->query($sql);

            if(!$res)
                dol_print_error($db);
            if($db->num_rows($res)>0)
                while($obj = $db->fetch_object($res)){
                    $inserted_questions[$obj->query_id] = $obj->rowid;
                }
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
            SendTaskForPurchase($order_id);
            $sql = 'update llx_orders set `status` = 1 where rowid='.$order_id;
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            exit();
        }break;
        case 'get_question':{
            $questions = ShowQuestion($_REQUEST['id_cat'], $_REQUEST['page']);
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
llxFooter();
exit();
function ShowQuestion($id_cat, $page){
    global $db, $user;
    $sql = 'select llx_c_category_product_question.rowid, llx_c_category_product_question.category_id, question, page from `llx_c_category_product_question`
        where llx_c_category_product_question.category_id = '.$id_cat.'
        and llx_c_category_product_question.active = 1
        and page = '.$page;
    $res_queries = $db->query($sql);
    if(!$res_queries)
        dol_print_error($db);
    $sql = 'select `llx_orders_queries`.`query_id`, `llx_orders_queries`.`answer` from `llx_orders`
        left join `llx_orders_queries` on `llx_orders_queries`.`order_id` = `llx_orders`.`rowid`
        inner join `llx_c_category_product_question` on  `llx_orders_queries`.`query_id` =`llx_c_category_product_question` .`rowid`
        where `llx_orders`.id_usr = '.$user->id.'
        and `llx_orders`.`status` = 0
        and `llx_c_category_product_question`.`category_id` is not null
        and `llx_c_category_product_question`.`active` = 1';
    $res_answer = $db->query($sql);
    if(!$res_answer)
        dol_print_error($db);
    $answer = array();
    while($obj = $db->fetch_object($res_answer)){
        $answer[$obj->query_id] = trim($obj->answer);
    }
    $out = '<table class="WidthScroll"> <tbody id="queries">';
    $num = 1;
    while($obj = $db->fetch_object($res_queries)){
        $class = (fmod($num++, 2)==0?'impair':'pair');
        $out .= '<tr class="'.$class.'">';
        $out .= '<td>'.$obj->question.'</td>';
        $out .= '</tr>';
        $class = (fmod($num++, 2)==0?'impair':'pair');
        $out .= '<tr class="'.$class.'">';
        $out .= '<td><textarea id="answer'.$obj->rowid.'" style="width: 90%">'.(isset($answer[$obj->rowid])?$answer[$obj->rowid]:'').'</textarea></td>';
        $out .= '</tr>';
    }
    $out.= '</tbody></table>';
    return $out;
}
function ShowOrders(){
    global $db, $user;
    $sql = 'select `llx_orders`.`rowid`, `llx_orders`.`dtCreated`, `llx_societe`.`nom` customer,
        max(`llx_actioncomm`.`datep`) as date_exec, `llx_orders`.`status`
        from `llx_orders`
        left join `llx_societe`on `llx_societe`.`rowid`=`llx_orders`.`socid`
        left join `llx_actioncomm` on `llx_actioncomm`.`fk_order_id`=`llx_orders`.`rowid`
        where `llx_orders`.`id_usr` = '.$user->id.'
        and `llx_orders`.`status` in (0,1,2)
        group by `llx_orders`.`rowid`, `llx_orders`.`dtCreated`, `llx_societe`.`nom`
        order by dtCreated desc';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
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
        $out .= '<td class="small_size"><img src="theme/eldy/img/preview.png" title="Переглянути" style="cursor: pointer">&nbsp;
                                        <img src="theme/eldy/img/object_user.png" title="Докладно по постачальникам" style="cursor: pointer">&nbsp;
                                        <img src="theme/eldy/img/edit.png" title="Редагувати" style="cursor: pointer">&nbsp;
                                        <img src="theme/eldy/img/delete.png" title="Видалити" style="cursor: pointer"></td>';
        $out .= '</tr>';
    }
    $out .= '</tbody>';
    return $out;
}
function ShowPrepareOrder($orders_id = 0){
		global $db, $user;
		$sql = 'select products_id from llx_orders ';
		if(empty($orders_id))
			$sql .= 'where status = 0 and id_usr = '.$user->id;
		else
			$sql .= 'where rowid = '.$orders_id;
		$sql .= ' limit 1';
		$res = $db->query($sql);
		$out = '';
		$obj = $db->fetch_object($res);
		$productlist = explode(';', $obj->products_id);
		$products = array();
		foreach($productlist as $product=>$value){
			if(!empty($value)) {
				$item = explode('=', $value);
				$products[$item[0]]=$item[1];
			}
		}
		$out .= '<tbody>';
        $product_class = new Product($db);
		$result_table = $product_class->ShowProducts(0, implode(',', array_keys($products)), 'name');
		$pos = 0;

		while(gettype(strpos($result_table, '<tr id="tr', $pos)) == 'integer') {
			$pos = strpos($result_table, '<tr id="tr', $pos);
			$product_id = substr($result_table, $pos+10, strpos($result_table, '"', $pos+10)-($pos+10));
			$result_table = substr($result_table, 0, strpos($result_table, '</tr>', $pos+10)).
					'<td id="art'.$product_id.'"></td>
					 <td id="Col'.$product_id.'" style="width:50px; text-align: center">'.$products[$product_id].'</td>
					 <td id="Ed'.$product_id.'"></td>
					 <td class="basic_part"></td>
					 <td class="basic_part"></td>
					 <td class="basic_part"></td>
					 <td class="basic_part"></td>
					 <td class="basic_part"></td>
					 <td class="basic_part"></td>
					 <td class="basic_part"></td>
					 <td class="basic_part"></td>
					 <td class="basic_part"></td>
					 <td class="basic_part"></td>
					 <td class="basic_part"></td>
					 <td class="basic_part"></td>
					 <td class="basic_part"></td>
					 <td class="addition_part"></td>
					 <td class="addition_part"></td>
					 <td class="addition_part"></td>
					 <td class="addition_part"></td>
					 <td class="addition_part"></td>
					 <td class="addition_part"></td>
					 <td class="addition_part"></td>
					 <td class="addition_part"></td>
					 <td class="addition_part"></td>
					 <td class="addition_part"></td>
					 <td class="result_part"></td>
					 <td class="result_part"></td>
					 <td class="result_part"></td>
					 <td class="result_part"></td>
					 <td class="features_part"></td>
					 <td class="features_part"></td>
					 <td class="features_part"></td>
					 <td class="features_part"></td>
					 <td class="features_part"></td>
					'.substr($result_table, strpos($result_table, '</tr>', $pos+10));
			$pos++;
		}
		$out .= $result_table;
		$out .= '</tbody>';
		return $out;
}
function GetQuestion($order_id){
    global $db;
    $sql = 'select `llx_c_category_product_question`.question, `llx_orders_queries`.`answer`
    from `llx_orders_queries`
    inner join `llx_c_category_product_question` on `llx_c_category_product_question`.`rowid`=`llx_orders_queries`.`query_id`
    where `llx_orders_queries`.`order_id` = '.$order_id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '';
    while($obj = $db->fetch_object($res)){
        $out .= '<tr><td><b>'.$obj->question.'</b></td></tr>';
        $out .= '<tr><td>'.$obj->answer.'</td></tr>';
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
function SendTaskForPurchase($order_id){
    global $db, $user;
    $sql = 'select products_id from `llx_orders` where rowid = '.$order_id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $productlist = explode(';', $obj->products_id);
    $products = array();
    foreach($productlist as $item){
        $itemarray = explode('=', $item);
        if(count($itemarray) == 2){
            $products[$itemarray[0]] = $itemarray[1];
        }
    }
    $sql = 'select distinct fk_user from `llx_user_lineactive`
        where fk_lineactive in
        (select distinct category_id from `oc_product_to_category`
        where product_id in ('.implode(',', array_keys($products)).'))
        and active = 1';
    $res = $db->query($sql);
    require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
    while($obj = $db->fetch_object($res)){
        $id_usr = $obj->fk_user;
//        var_dump($user->id, $id_usr);
        //http://"+location.hostname+"/dolibarr/htdocs/comm/action/card.php?action=get_freetime&date="+$("#apyear").val()+"-"+$("#apmonth").val()+"-"+$("#apday").val()+"&id_usr="+$("#id_usr").val()+"&minute="+minute;
        $action = new ActionComm($db);
        $exec_minuted = $action->GetExecTime('AC_CURRENT');
        $freetime = $action->GetFirstFreeTime(date('Y-m-d'), $id_usr, $exec_minuted);
        $date = new DateTime(date('Y-m-d').' '.$freetime);
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
