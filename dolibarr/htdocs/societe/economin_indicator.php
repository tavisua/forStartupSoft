<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 15.12.2015
 * Time: 10:53
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
//var_dump($_REQUEST);
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
    $EconomicIndicators->tech_param     = GETPOST('tech_param', 'alpha');
    $EconomicIndicators->productivity   = GETPOST('productivity', 'alpha');
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
//    llxHeader();
//    echo '<pre>';
//    var_dump($EconomicIndicators);
//    echo '</pre>';
//    die();
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
}elseif($action == 'editparam'){
    $Title = $langs->trans("EditParameters");
    llxHeader('',$Title,$help_url);
    print_fiche_titre($Title);
    $EconomicIndicators->fetch_fixed_assets($_REQUEST['rowid']);
    $soc_contact = $EconomicIndicators;
//    echo '<pre>';
//    var_dump($EconomicIndicators);
//    echo '</pre>';
//    die();
    $action_url = $_SERVER['PHP_SELF'];
    include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/addparam.html';
    exit();
}



$Title = $langs->trans("EconomicIndicators");
llxHeader('',$Title,$help_url);
print_fiche_titre($Title);

print '
        <div class="tabs" data-role="controlgroup" data-type="horizontal">
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/soc.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('BasicInfo').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societeaddress.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('AddressList').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societecontact.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('ContactList').'</a>
            </div>';
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

            print '<div class="inline-block tabsElem">
                            <a id="user" class="tabactive tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/economin_indicator.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('EconomicData').'</a>
                        </div>';
            if(in_array($object->categoryofcustomer_id, $purchase_category)||in_array($object->categoryofcustomer_id, $marketing_category)) {
                print '<div class="inline-block tabsElem">
                                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/lineactive.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('LineActive').'</a>
                            </div>';
            }
print '<div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/finance.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&socid='.$object->id.'">'.$langs->trans('FinanceAndDetails').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/partners.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&socid='.$object->id.'">'.$langs->trans('PartnersOfCustomer').'</a>
            </div>
        </div>';

//if(in_array($object->categoryofcustomer_id, $sales_category)){
    include($_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/' . $conf->theme . '/economic_indicator.html');
    print '<script>
    function preview(object){
//        console.log($("#L"+object.id.substr(2)).attr("id"));
        if(object.id.substr(0,2)=="m_")
            $("#prev_form").text($("#L"+object.id.substr(2)).text());
        else
            $("#prev_form").text($("#L"+object.id).text());
        location.href="#peview_form";
    }
</script>';
    $prev_form = "<a href='#x' class='overlay' id='peview_form'></a>
                     <div class='popup' style='width: 300px;height: 150px'>
                     <textarea readonly id='prev_form' style='width: 100%;height: 100%;resize: none'></textarea>
                     <a class='close' title='Закрыть' href='#close'></a>
                     </div>";
    print $prev_form;
//}

echo ob_get_clean();
//llxFooter();