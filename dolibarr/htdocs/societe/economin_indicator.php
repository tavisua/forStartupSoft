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

//echo '<pre>';
//var_dump($socid);
//echo '</pre>';

$object = new  Societe($db);

$object->fetch($socid);

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
    }
    exit();
}elseif($action == 'add'){

    $Title = $langs->trans("AddParameters");
    llxHeader('',$Title,$help_url);
    print_fiche_titre($Title);
    $action_url = $_SERVER['PHP_SELF'];
    include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/addparam.html';
    exit();
}


$Title = $langs->trans("EconomicIndicators");
llxHeader('',$Title,$help_url);
print_fiche_titre($Title);



include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/economic_indicator.html');
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
echo ob_get_clean();
llxFooter();