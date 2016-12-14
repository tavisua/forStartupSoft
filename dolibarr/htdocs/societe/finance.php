<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 29.01.2016
 * Time: 16:34
 */
require '../main.inc.php';
global $user, $db;

//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//die();
if(empty($socid)) {
    $socid = $_REQUEST['socid'];
    if(empty($socid)){
        $search = explode('&', substr($_REQUEST["url"], strpos($_REQUEST["url"], '?')+1));
        foreach ($search as $item) {
            if(strpos($item, 'socid') !== false) {
                $socid = substr($item, strpos($item, '=')+1);
                $_REQUEST['socid'] = $socid;
//                echo '<pre>';
//                var_dump($socid, $item, strpos($item, 'socid'));
//                echo '</pre>';
            }
        }
//        die();
    }
}
$action = $_REQUEST['action'];

$object = new Societe($db);
$object->fetch($socid);
$form = new Form($db);

if($action == 'add') {
    $AddItem = $langs->trans('AddInform');
    llxHeader('', $AddItem, $help_url);
    print_fiche_titre($AddItem);
    $sql = 'select "UAH" as fx_account_curr, "UAH" as fx_finance_curr, 7 as categoryofcustomer_id, '.$socid.' as fx_soc';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $item = $db->fetch_object($res);

    include($_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/' . $conf->theme . '/addfinance.html');
    echo ob_get_clean();
//    llxFooter();
    exit();
}elseif($action == 'edit'){
//    echo '<pre>';
//    var_dump($_SERVER);
//    echo '</pre>';

    $EditInform = $langs->trans('EditInform');
    llxHeader('', $EditInform, $help_url);
    print_fiche_titre($EditInform);
    global $db, $langs, $conf;
    $sql='select llx_societe_finance.rowid, llx_user.lastname, llx_societe_finance.fx_soc, llx_societe_finance.socid, llx_societe_finance.dtChange, llx_societe.categoryofcustomer_id,
    llx_societe.nom, llx_societe.address, llx_societe_finance.`account`, `fx_account_curr`,
    `mfo`,llx_societe_finance.fx_account_service,`account_width`,`fx_finance_curr`,`comment`,`comment_with_finservice`,
    `comment_about_reliability`,`comment_from_finservice`,`erdpou`,`inn`,`certificate_number`
    from llx_societe_finance
    left join llx_user on llx_user.rowid = llx_societe_finance.id_usr
    left join llx_societe on llx_societe_finance.socid = llx_societe.rowid
    left join `category_counterparty` on `category_counterparty`.rowid=llx_societe.`categoryofcustomer_id`
    where llx_societe_finance.rowid='.$_REQUEST['rowid'];
//    die($sql);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $item = $db->fetch_object($res);



    include($_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/' . $conf->theme . '/addfinance.html');

//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
    exit();
}elseif($action == 'save'){
    if(empty($_REQUEST['fx_soc'])){
        $finance = new Societe($db);
        $finance->name = $_REQUEST['nom'];
        $finance->categoryofcustomer_id=$_REQUEST['categoryofcustomer'];
        $finance->create($user);
        $_REQUEST['fx_soc']=$finance->id;
    }
    if(empty($_REQUEST['rowid'])){
//        echo '<pre>';
//        var_dump($_REQUEST);
//        echo '</pre>';
//        die();
        $sql = 'insert into llx_societe_finance(socid,fx_soc,account,fx_account_curr,mfo,fx_account_service,
        account_width,fx_finance_curr,comment,comment_with_finservice,comment_about_reliability,
        comment_from_finservice,erdpou,inn,certificate_number,id_usr) values('.
            $_REQUEST['socid'].', '.$_REQUEST['fx_soc'].', '.
            (empty($_REQUEST['accountnumber'])?'null':("'".$db->escape($_REQUEST['accountnumber']))."'").', "'.$_REQUEST['acount_curr'].'",
            '.(empty($_REQUEST['mfo'])?'null':("'".$db->escape($_REQUEST['mfo']))."'").', '.$db->escape($_REQUEST['finance_service']).','.
            (empty($_REQUEST['account_width'])?"null":"'".$_REQUEST['account_width']."'").',"'
            .$_REQUEST['finance_curr'].'", "'.$db->escape($_REQUEST['comment']).'", "'.$_REQUEST['comment_with_finservice'].'", "'.
            $_REQUEST['comment_about_reliability'].'", "'.$db->escape($_REQUEST['comment_from_finservice']).'", '.
            (empty($_REQUEST['erdpou'])?'null':"'".$_REQUEST['erdpou']."'").', '.
            (empty($_REQUEST['inn'])?'null':"'".$_REQUEST['inn']."'").', '.
            (empty($_REQUEST['certificate_number'])?"null":"'".$db->escape($_REQUEST['certificate_number'])."'").', '.$user->id.')';
    }else{
        $sql = "update llx_societe_finance set
        socid=".$_REQUEST['socid'].",fx_soc=".$_REQUEST['fx_soc'].",
        account=".(empty($_REQUEST['accountnumber'])?'null':$_REQUEST['accountnumber']).",
        fx_account_curr='".$_REQUEST['acount_curr']."',
        mfo=".(empty($_REQUEST['mfo'])?'null':$_REQUEST['mfo']).",
        fx_account_service=".$_REQUEST['finance_service'].",
        account_width=".(empty($_REQUEST['account_width'])?"null":$_REQUEST['account_width']).",
        fx_finance_curr='".$_REQUEST['finance_curr']."',
        comment='".$_REQUEST['comment']."',
        comment_with_finservice='".$_REQUEST['comment_with_finservice']."',
        comment_about_reliability='".$_REQUEST['comment_about_reliability']."',
        comment_from_finservice='".$_REQUEST['comment_from_finservice']."',
        erdpou=".(empty($_REQUEST['erdpou'])?'null':$_REQUEST['erdpou']).",
        inn=".(empty($_REQUEST['inn'])?'null':$_REQUEST['inn']).",
        certificate_number=".(empty($_REQUEST['certificate_number'])?"null":$_REQUEST['certificate_number']).",
        id_usr=".$user->id." where rowid=".$_REQUEST['rowid'];
    }
//    llxHeader();
//    die($sql);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
    header('Location: ' . $_REQUEST['url']);
    exit;
}


$Title = $langs->trans("FinanceAndDetails");
llxHeader('',$Title,$help_url);
print_fiche_titre($Title);

//echo '<pre>';
//var_dump($object);
//echo '</pre>';
//die();
print '
        <div class="tabs" data-role="controlgroup" data-type="horizontal">
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/soc.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$_REQUEST['socid'].'">'.$langs->trans('BasicInfo').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societeaddress.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$_REQUEST['socid'].'">'.$langs->trans('AddressList').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societecontact.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$_REQUEST['socid'].'">'.$langs->trans('ContactList').'</a>
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
                                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/economin_indicator.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('EconomicData').'</a>
                            </div>';
            if(in_array($object->categoryofcustomer_id, $purchase_category)||in_array($object->categoryofcustomer_id, $marketing_category)) {
                print '<div class="inline-block tabsElem">
                                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/lineactive.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('LineActive').'</a>
                            </div>';
            }
        print '<div class="inline-block tabsElem">
                <a id="user" class="tabactive tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/finance.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&socid='.$_REQUEST['socid'].'">'.$langs->trans('FinanceAndDetails').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/partners.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&socid='.$_REQUEST['socid'].'">'.$langs->trans('PartnersOfCustomer').'</a>
            </div>
        </div>';
$table = ShowTable();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/societe_finance.html');
exit();

function ShowTable(){
    global $db, $langs, $conf;
    $sql='select llx_societe_finance.rowid, llx_user.lastname, llx_societe_finance.dtChange,category_counterparty.name as category_counterparty,
    llx_societe.nom, llx_societe.address, llx_societe_finance.`account`, `fx_account_curr`,
    `mfo`,llx_c_finance_service.name `fx_account_service`,`account_width`,`fx_finance_curr`,`comment`,`comment_with_finservice`,
    `comment_about_reliability`,`comment_from_finservice`,`erdpou`,`inn`,`certificate_number`
    from llx_societe_finance
    left join llx_user on llx_user.rowid = llx_societe_finance.id_usr
    left join llx_societe on llx_societe_finance.socid = llx_societe.rowid
    left join `category_counterparty` on `category_counterparty`.rowid=llx_societe.`categoryofcustomer_id`
    left join `llx_c_finance_service` on `llx_c_finance_service`.rowid=llx_societe_finance.fx_account_service
    where llx_societe_finance.fx_soc = '.$_REQUEST['socid'];
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out='<tbody>';
    $num = 0;
    while($obj=$db->fetch_object($res)){
        $date = new DateTime($obj->dtChange);
        $out.='<tr id="'.$obj->rowid.'" class = "'.(fmod($num++, 2)==0?'impair':'pair').' small_size">
            <td>'.$obj->lastname.'</td>
            <td title="'.$date->format('d.m.y').'">'.$date->format('d.m').'</td>
            <td>'.$obj->category_counterparty.'</td>
            <td>'.$obj->nom.'</td>
            <td>'.$obj->address.'</td>
            <td>'.$obj->account.'</td>
            <td>'.$langs->trans('Currency'.$obj->fx_account_curr).'</td>
            <td>'.$obj->mfo.'</td>
            <td>'.$obj->fx_account_service.'</td>
            <td>'.$obj->account_width.'</td>
            <td>'.$langs->trans('Currency'.$obj->fx_finance_curr).'</td>
            <td>'.$obj->comment.'</td>
            <td>'.$obj->comment_with_finservice.'</td>
            <td>'.$obj->comment_about_reliability.'</td>
            <td>'.$obj->comment_from_finservice.'</td>
            <td>'.$obj->erdpou.'</td>
            <td>'.$obj->inn.'</td>
            <td>'.$obj->certificate_number.'</td>
            <td style="width: 20px" align="left">
                <img  id="img_'. $obj->rowid.'" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/edit.png" title="'.$langs->trans('Edit').'" style="vertical-align: middle" onclick="edit_item(' . $obj->rowid . ');">
            </td>
        </tr>';
    }
    $out.='</tbody>';
    return $out;
}
