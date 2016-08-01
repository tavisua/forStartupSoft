<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 14.04.2016
 * Time: 17:53
 */
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/proposedProducts.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

$action = $_REQUEST['action'];
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
$proposedPoducts = new proposedProducts($db);
$form = new Form($db);
if($action == 'create') {
    $title = $langs->trans('AddProposedProduct');
    llxHeader("", $title, "");
    print_fiche_titre($title);
    $action = 'add';
    $grouparray = array();
    include DOL_DOCUMENT_ROOT . '/theme/eldy/admin/addProposedProducts.html';
    exit();
}elseif($action == 'del'){
    $proposedPoducts->del($_REQUEST['rowid']);
    echo 1;
    exit();
}elseif($action == 'edit'){
    $proposedPoducts->fetchProductsItem($_REQUEST['rowid']);
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();

    $grouparray = array($proposedPoducts->fx_category);
    $title = $langs->trans('EditProposedProduct');
    llxHeader("", $title, "");
    print_fiche_titre($title);
    $action = 'update';
    include DOL_DOCUMENT_ROOT . '/theme/eldy/admin/addProposedProducts.html';
    exit();
}elseif($action == 'update'){
    $proposedPoducts->rowid          =  $_REQUEST['rowid'];
    $proposedPoducts->fx_proposition =  $_REQUEST['proposed_id'];
    $proposedPoducts->fx_category    =  $_REQUEST['category'];
    $proposedPoducts->Prodaction     =  $_REQUEST['Prodaction'];
    $proposedPoducts->ProductName    =  $_REQUEST['ProductName'];
    $proposedPoducts->articul        =  $_REQUEST['articul'];
    $proposedPoducts->Number1C       =  $_REQUEST['Number1C'];
    $proposedPoducts->Nal            =  $_REQUEST['Nal'];
    $proposedPoducts->ed_izm         =  $_REQUEST['ed_izm'];
    $proposedPoducts->shipTown       =  $_REQUEST['shipTown'];
    $proposedPoducts->featureOffers  =  $_REQUEST['featureOffers'];
    $proposedPoducts->profitCustomer =  $_REQUEST['profitCustomer'];
    $proposedPoducts->price          =  $_REQUEST['price'];
    $proposedPoducts->offerPrice     =  $_REQUEST['offerPrice'];
    $proposedPoducts->advance        =  $_REQUEST['advance'];
    $proposedPoducts->deadlineAdvance=  $_REQUEST['deadlineAdvance'];
    $proposedPoducts->deadlineSale   =  $_REQUEST['deadlineSale'];
    $proposedPoducts->dateExec       =  $_REQUEST['dateExec'];
    $proposedPoducts->delivary       =  $_REQUEST['delivary'];
    $proposedPoducts->otherDiscont   =  $_REQUEST['otherDiscont'];
    $proposedPoducts->description    =  $_REQUEST['description'];
    $proposedPoducts->update();
    header("Location: http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/admin/proposedProducts.php?proposed_id=".$_REQUEST['proposed_id']."&idmenu=5223&mainmenu=tools&leftmenu=");
    exit();
}elseif($action == 'add'){
    $proposedPoducts->fx_proposition =  $_REQUEST['proposed_id'];
    $proposedPoducts->fx_category    =  $_REQUEST['category'];
    $proposedPoducts->Prodaction     =  $_REQUEST['Prodaction'];
    $proposedPoducts->ProductName    =  $_REQUEST['ProductName'];
    $proposedPoducts->articul        =  $_REQUEST['articul'];
    $proposedPoducts->Number1C       =  $_REQUEST['Number1C'];
    $proposedPoducts->Nal            =  $_REQUEST['Nal'];
    $proposedPoducts->ed_izm         =  $_REQUEST['ed_izm'];
    $proposedPoducts->shipTown       =  $_REQUEST['shipTown'];
    $proposedPoducts->featureOffers  =  $_REQUEST['featureOffers'];
    $proposedPoducts->profitCustomer =  $_REQUEST['profitCustomer'];
    $proposedPoducts->price          =  $_REQUEST['price'];
    $proposedPoducts->offerPrice     =  $_REQUEST['offerPrice'];
    $proposedPoducts->advance        =  $_REQUEST['advance'];
    $proposedPoducts->deadlineAdvance=  $_REQUEST['deadlineAdvance'];
    $proposedPoducts->deadlineSale   =  $_REQUEST['deadlineSale'];
    $proposedPoducts->dateExec       =  $_REQUEST['dateExec'];
    $proposedPoducts->delivary       =  $_REQUEST['delivary'];
    $proposedPoducts->otherDiscont   =  $_REQUEST['otherDiscont'];
    $proposedPoducts->description    =  $_REQUEST['description'];
//    echo '<pre>';
//    var_dump($proposedPoducts);
//    echo '</pre>';
//    die();
    $proposedPoducts->add();
    header("Location: http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/admin/proposedProducts.php?proposed_id=".$_REQUEST['proposed_id']."&idmenu=5223&mainmenu=tools&leftmenu=");
    exit();
}

$title = $langs->trans('ProposedProducts');
llxHeader("",$title,"");
print_fiche_titre($title);

$proposed = $proposedPoducts->fetch($_REQUEST['proposed_id']);
$begin = new DateTime($proposed->begin);
//print 'Початок дії'.$begin->format('d.m.y').'</br>';
if(!empty($proposed->end)) {
    $end = new DateTime($proposed->end);
    $end = $end->format('d.m.y');
}else
    $end = $proposed->description;
print '<table class="middle_size">
    <tbody>
        <tr>
            <td><b>Початок дії</b></td>
            <td>'.$begin->format('d.m.y').'</td>
        </tr>
        <tr>
            <td><b>Кінець дії</b></td>
            <td>'.$end.'</td>
        </tr>
    </tbody>
</table>';
print '<div class="titre">'.$proposed->title.'</div></br>';
$tabody = $proposedPoducts->ShowProducts($_REQUEST['proposed_id']);
include DOL_DOCUMENT_ROOT.'/theme/eldy/admin/proposedProducts.html';
llxPopupMenu();

