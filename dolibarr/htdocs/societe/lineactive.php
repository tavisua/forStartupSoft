<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 31.08.2016
 * Time: 6:38
 */
require '../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/economic_indicator_class.php';

$socid = GETPOST('socid', 'int');
if(empty($socid))
    $socid = $_REQUEST['socid'];

$url = $_SERVER["HTTP_REFERER"];


$object = new  Societe($db);

$object->fetch($socid);
//echo '<pre>';
//var_dump($object);
//echo '</pre>';
//die();
$CategoryOfCustomer = $object->getCategoryOfCustomer();
$FormOfGoverment = $object->getFormOfGoverment();
$EconomicIndicators = new EconomicIndicator($socid);

$action = GETPOST('action', 'alpha');
if($_REQUEST['action'] == 'get_economic_indicators') {
    print $EconomicIndicators->get_economic_indicators($_REQUEST['line_active']);
    exit();
}elseif($_REQUEST['action'] == 'get_kind_assets'){
    print $EconomicIndicators->selectkind_assets($_REQUEST['line_active']);
    exit();
}elseif($_REQUEST['action'] == 'get_model'){
    print $EconomicIndicators->selectmodel($_REQUEST['trademark'],$_REQUEST['kindassets']);
    exit();
}elseif($action == 'save' || $action == 'save_and_add'){
//    echo '<pre>';
//    var_dump($_POST);
//    echo '</pre>';
//    die();
    $EconomicIndicators->socid = $socid;
    $EconomicIndicators->rowid          = GETPOST('rowid', 'int');
    $EconomicIndicators->contact        = GETPOST('contact', 'int');
    $EconomicIndicators->container      = GETPOST('container', 'alpha');
    $EconomicIndicators->line_active    = GETPOST('lineactive', 'int');
    $EconomicIndicators->kindassets     = GETPOST('KindAssets', 'int');
    $EconomicIndicators->trademark      = GETPOST('trademark', 'int');
    $EconomicIndicators->for_what       = GETPOST('for_what', 'alpha');
    $EconomicIndicators->count          = GETPOST('count', 'alpha');
    $EconomicIndicators->year           = GETPOST('year', 'int');
    if(empty($EconomicIndicators->year))$EconomicIndicators->year = 'null';
    $EconomicIndicators->container      = GETPOST('container', 'int');
    $EconomicIndicators->time_purchase  = GETPOST('time_purchase', 'int');
    if(empty($EconomicIndicators->time_purchase))$EconomicIndicators->time_purchase=0;
    $EconomicIndicators->rate           = GETPOST('rate', 'int');
    $EconomicIndicators->time_purchase2 = GETPOST('time_purchase2', 'int');
    if(empty($EconomicIndicators->time_purchase2))$EconomicIndicators->time_purchase2=0;
    $EconomicIndicators->rate2          = GETPOST('rate2', 'int');
    $EconomicIndicators->PositiveResponse   = GETPOST('PositiveResponse', 'alpha');
    $EconomicIndicators->NegativeResponse   = GETPOST('NegativeResponse', 'alpha');
    $EconomicIndicators->model          = GETPOST('model', 'int');
    $EconomicIndicators->UnMeasurement  = GETPOST('UnMeasurement', 'int');
    if(empty($EconomicIndicators->UnMeasurement))$EconomicIndicators->UnMeasurement=0;
    $EconomicIndicators->ContainerUnMeasurement = GETPOST('ContainerUnMeasurement', 'int');
    if(empty($EconomicIndicators->ContainerUnMeasurement))$EconomicIndicators->ContainerUnMeasurement=0;
    $EconomicIndicators->saveitem();//Сохраняю изменения

    if( $action == 'save_and_add') {
        $action = 'add';
        $Title = $langs->trans("AddParameters");
        llxHeader('',$Title,$help_url);
        print_fiche_titre($Title);
        $action_url = $_SERVER['PHP_SELF'];
        $EconomicIndicators->rowid          = 0;
        include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/addparam.html';
    }else
        header('Location: '.DOL_URL_ROOT.'/societe/economin_indicator.php?mainmenu=area&idmenu=10425&action=edit&socid='.$socid);
    exit();
}elseif($action == 'add'){

    $Title = $langs->trans("AddParameters");
    llxHeader('',$Title,$help_url);
    print_fiche_titre($Title);
    $action_url = $_SERVER['PHP_SELF'];
    include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/addparam.html';
    exit();
}elseif(isset($_REQUEST['actionlineactive']) && $_REQUEST['actionlineactive'] == 'updatelineactive'){
        global $user;
    $update_user = new User($db);
    $update_user->info($_REQUEST['id']);
    $lineactive = explode(',', $_REQUEST['values']);
    $sql = 'select fk_lineactive, rowid from llx_societe_lineactive where fk_soc='.$object->id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $user_lineactive = array();
    while($obj = $db->fetch_object($res)){
        $user_lineactive[$obj->fk_lineactive] = $obj->rowid;
    }
    $inserted_values = array_keys($user_lineactive);

    foreach($inserted_values as $item){//Помічаю на видалення
        if(!in_array($item, $lineactive)){
            $sql = 'update llx_societe_lineactive set active = 0, id_usr='.$user->id.
                ' where fk_soc='.$object->id.' and fk_lineactive='.$item.' limit 1';
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
        }
    }
    foreach($lineactive as $item){//Добавляю інші
        if(!isset($user_lineactive[$item]))
            $sql = 'insert into llx_societe_lineactive(fk_soc,fk_lineactive,active,id_usr)
            values('.$object->id.', '.$item.', 1, '.$user->id.')';
        else
            $sql = 'update llx_societe_lineactive set active = 1, id_usr='.$user->id.
                ' where fk_soc='.$object->id.' and fk_lineactive='.$item.' limit 1';
        $res = $db->query($sql);
//        if(!$res)
//            dol_print_error($db);
    }
}
$sql = "select case when `responsibility_param`.`fx_category_counterparty` is null then `responsibility_param`.`other_category` else `responsibility_param`.`fx_category_counterparty` end category_id, `responsibility`.`alias` from `responsibility`
                inner join `responsibility_param` on `responsibility_param`.`fx_responsibility` = `responsibility`.`rowid`
                where `responsibility`.`alias` in ('sale','purchase','marketing')";
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            $sales_category = array();
            $purchase_category = array();
            $marketing_category = array();
            while($obj = $db->fetch_object($res)){
                if(!empty($obj->category_id)) {
                    switch ($obj->alias) {
                        case 'sale': {
                            $sales_category[] = $obj->category_id;
                        }
                            break;
                        case 'purchase': {
                            $purchase_category[] = $obj->category_id;
                        }
                            break;
                        case 'marketing': {
                            $marketing_category[] = $obj->category_id;
                        }
                            break;
                    }
                }
            }

$Title = $langs->trans("LineActive");
llxHeader('',$Title,$help_url);
print_fiche_titre($Title);
if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit')
    $action = '&action=edit';
else
    $action = '';
print '
        <div class="tabs" data-role="controlgroup" data-type="horizontal">
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/soc.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('BasicInfo').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societeaddress.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('AddressList').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societecontact.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('ContactList').'</a>
            </div>';
            print '<div class="inline-block tabsElem">
                            <a id="user" class=" tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/economin_indicator.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('EconomicData').'</a>
                        </div>';
            print '<div class="inline-block tabactive tabsElem">
                                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/lineactive.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('LineActive').'</a>
                            </div>';

print '<div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/finance.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&socid='.$object->id.'">'.$langs->trans('FinanceAndDetails').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/partners.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&socid='.$object->id.'">'.$langs->trans('PartnersOfCustomer').'</a>
            </div>
        </div>';
    $lineactive=array();
    $sql = 'select fk_lineactive from llx_societe_lineactive where fk_soc = '.$object->id.' and active=1';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    if($db->num_rows($res)>0)
        while($obj = $db->fetch_object($res)){
            $lineactive[]=$obj->fk_lineactive;
        }

    require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
    $form = new Form($db);
    print '<div id="LineActive" class="tabPage">';
    print '    <div class="address_header">
        <table id="headercontrol" style="background-color: #ffffff">
            <tr>
                <td><b>Категорія контрагента</b></td>
                <td>'.$object->getCategoryOfCustomer().'</td>
            </tr>
            <tr>
                <td><b>Назва контрагента</b></td>
                <td>'.$object->name.'</td>
            </tr>
            <tr>
                <td><b>Форма правління</b></td>
                <td>'.$object->getFormOfGoverment().'</td>
            </tr>
        </table>
    </div>';

    print '<form id="lineaction" action="" method="post" style="width: 550px; padding-left: 200px">';
    print '<input id="id" name="id" value="'.$user->id.'" type="hidden">';
    print '<input id="mainmenu" name="mainmenu" value="'.$_REQUEST['mainmenu'].'" type="hidden">';
    print '<input id="idmenu" name="idmenu" value="'.$_REQUEST['idmenu'].'" type="hidden">';
    print '<input id="values" name="values" value="" type="hidden">';
    print '<input id="action" name="action" value="edit" type="hidden">';
    print '<input id="socid" name="socid" value="'.$_REQUEST['socid'].'" type="hidden">';
    print '<input id="actionlineactive" name="actionlineactive" value="updatelineactive" type="hidden">';
    if(in_array($object->categoryofcustomer_id, $purchase_category))
        print $form->selectLineAction($lineactive, 'select_lineaction', 30);
    elseif(in_array($object->categoryofcustomer_id, $marketing_category))
        print $form->select_lineaction_marketing($lineactive, 'select_lineaction', 30);
    print '</br>';
    if(!empty($action))
        print '<input type="submit" value="Зберегти">';
    print '</form>';
    print '</div>';
    print "<script>
        $(document).ready(function(){
            $('#select_lineaction').on('change', SelectLineaction);
        })
        function SelectLineaction(){
            $('#values').val($('#select_lineaction').val());
        }
    </script>";