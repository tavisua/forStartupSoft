<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 16.11.2015
 * Time: 9:55
 */

require '../main.inc.php';
//echo '<pre>';
//var_dump($_POST);
//echo '</pre>';

require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/societe/SocAddress.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
$soc_address = new SocAddress();
$form = new Form($db);
$formcompany = new FormCompany($db);
$action = GETPOST('action', 'alpha');
$action_url = str_replace('edit', 'save',$_SERVER['REQUEST_URI']);
//var_dump($action_url);
//die();
if($action =='cancel') {
    header('Location: ' . $_REQUEST['url']);
    exit;
}elseif($_REQUEST['action'] == 'edit'){
    $soc_address->fetch($_REQUEST['rowid']);

    $action      = 'save';
    $socid       = $soc_address->socid;
    $url         = $_SERVER["HTTP_REFERER"];
//    die($url);
    $EditAddress = $langs->trans('EditAddress');
    llxHeader('', $EditAddress, $help_url);
    print_fiche_titre($EditAddress);
    include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/addaddress.html');
    echo ob_get_clean();
//    llxFooter();
    exit;
}elseif(GETPOST('action', 'alpha') == 'save'){
    $url                        = htmlspecialchars(GETPOST('url', 'alpha'));
    $soc_address->rowid         = GETPOST('rowid');
    $soc_address->whom          = GETPOST('whom');
    $soc_address->kindaddress   = GETPOST('kindaddress');
    $soc_address->Zip           = GETPOST('zip');
    $soc_address->country_id    = GETPOST('country_id');
    $soc_address->state_id      = GETPOST('state_id');
    $soc_address->region_id     = GETPOST('region_id');
    $soc_address->kindlocality_id = GETPOST('kindlocality_id');
    $soc_address->location        = GETPOST('location');
    $soc_address->kindofstreet_id = GETPOST('kindofstreet_id');
    $soc_address->street_name     = GETPOST('street_name');
    $soc_address->NumberOfHouse   = GETPOST('numberofhouse');
    $soc_address->kindoffice_id   = GETPOST('kindoffice_id');
    $soc_address->NumberOfOffice  = GETPOST('numberofoffice');
    $soc_address->GPS           = GETPOST('GPS');
    $soc_address->e_mail        = GETPOST('e_mail');
    $soc_address->site          = GETPOST('site');
    $soc_address->WorkerCount   = GETPOST('WorkerCount');
    $soc_address->SendEmail     = GETPOST('SendEmail');
    $soc_address->SendPost      = GETPOST('SendPost');
    $error = 0;
    if(empty($soc_address->whom))$soc_address->whom='';
//    if(empty($soc_address->kindaddress))$error++;
    if(empty($soc_address->Zip))$soc_address->Zip='null';
//    if($soc_address->country_id == 0)$error++;
//    if($soc_address->state_id == 0)$error++;
//    if($soc_address->region_id == 0)$error++;
//    if($soc_address->kindlocality_id == 0)$error++;
    if(empty($soc_address->location))$soc_address->location='';
//    if($soc_address->kindofstreet_id == 0)$error++;
    if(empty($soc_address->street_name))$soc_address->street_name='';
    if(empty($soc_address->NumberOfHouse))$soc_address->NumberOfHouse='';
    if(empty($soc_address->NumberOfOffice))$soc_address->NumberOfOffice='';
    if(empty($soc_address->WorkerCount))$soc_address->WorkerCount='null';
    if($soc_address->kindoffice_id == 0)$soc_address->kindoffice_id=0;

//    if(empty($soc_address->whom))$error++;
//    if(empty($soc_address->kindaddress))$error++;
//    if(empty($soc_address->Zip))$error++;
//    if($soc_address->country_id == 0)$error++;
//    if($soc_address->state_id == 0)$error++;
//    if($soc_address->region_id == 0)$error++;
//    if($soc_address->kindlocality_id == 0)$error++;
//    if(empty($soc_address->location))$error++;
//    if($soc_address->kindofstreet_id == 0)$error++;
//    if(empty($soc_address->street_name))$error++;
//    if(empty($soc_address->NumberOfHouse))$error++;
//    if(empty($soc_address->NumberOfOffice))$error++;
//    if($soc_address->kindoffice_id == 0)$error++;
    if($error > 0) {
        $action = 'error';
    }else {
        if(empty($soc_address->rowid)){
            $soc_address->createAddress(GETPOST('socid'));
        }else{
            $soc_address->updateAddress();
        }
        header('Location: ' . GETPOST('url', 'alpha'));
        exit();
    }
//    echo ('<pre>');
//    var_dump($soc_address);
//    echo ('</pre>');
//    die();
}

$socid = GETPOST('socid', 'int');
if(empty($socid))
    $socid = $_REQUEST['socid'];

$url = $_SERVER["HTTP_REFERER"];


$object = new  Societe($db);
$object->fetch($socid);
$CategoryOfCustomer = $object->getCategoryOfCustomer();
$FormOfGoverment = $object->getFormOfGoverment();
if(GETPOST('action', 'alpha') == 'add' ||$action == 'error') {
    $soc_address->state_id = $object->state_id;
    $soc_address->country_id = $object->country_id;
    $soc_address->region_id = $object->region_id;
//    echo '<pre>';
//    var_dump($object);
//    var_dump($soc_address);
//    echo '</pre>';
//    die();
    $AddAddress = $langs->trans('AddAddress');
    llxHeader('', $AddAddress, $help_url);
    print_fiche_titre($AddAddress);
    include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/addaddress.html');
    echo ob_get_clean();
//    llxFooter();
}


