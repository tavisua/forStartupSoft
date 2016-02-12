<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 09.12.2015
 * Time: 9:59
 */

require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

$Orders = $langs->trans('Orders');
llxHeader("",$Orders,"");
print_fiche_titre($langs->trans('Orders'));


if(isset($_REQUEST['type_action'])){
    switch($_REQUEST['type_action']){
        case 'with_list':{
            $actionform=ShowPriceList();
        }break;
        case 'without_list':{

        }break;
        case 'internal':{

        }break;
        case 'choose_product':{

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
