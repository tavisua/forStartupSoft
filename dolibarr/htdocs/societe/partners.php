<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 31.01.2016
 * Time: 13:22
 */
require '../main.inc.php';
require DOL_DOCUMENT_ROOT.'/societe/economic_indicator_class.php';
if(empty($socid))
    $socid = $_REQUEST['socid'];
$action = $_REQUEST['action'];
//echo '<pre>';
//var_dump($_SERVER);
//echo '</pre>';
//die();
$object = new Societe($db);
$object->fetch($socid);
$form = new Form($db);

if($action == 'add') {
    $AddItem = $langs->trans('AddInform');
    llxHeader('', $AddItem, $help_url);
    print_fiche_titre($AddItem);
    $EconomicIndicators = new EconomicIndicator($socid);
    $sql = 'select 9 categoryofcustomer_id';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $item = $db->fetch_object($res);
    include($_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/' . $conf->theme . '/addpartner.html');
    echo ob_get_clean();
//    llxFooter();
    exit();
}elseif($action == 'edit'){

    $EconomicIndicators = new EconomicIndicator($socid);
    $EditInform = $langs->trans('EditInform');
    llxHeader('', $EditInform, $help_url);
    print_fiche_titre($EditInform);
    global $db, $langs, $conf;
    $sql='select `llx_societe_partners`.rowid, llx_societe.`categoryofcustomer_id`,
        category_counterparty.name catname, `llx_societe_partners`.dCol_ed, llx_societe_partners.fx_soc, llx_societe.nom contragentname,
        `llx_c_kind_assets`.`fx_line_active` as line_active, `llx_c_model`.`fx_kind_assets` as kind_assets,
        `llx_c_trademark`.`rowid` trademark, llx_societe_partners.fx_model as model,
         account_date,account_number,discont_from_price,
        discont_from_units,price,percent_payment,delay,delivery,guarantee,present,money_turnover,`comment`, `llx_c_tare`.rowid as tare,
        `llx_societe_partners`.`fx_measurement`, `llx_societe_partners`.`fx_tare_measurement`
        from `llx_societe_partners`
        left join llx_user on llx_user.rowid = llx_societe_partners.id_usr
        left join llx_societe on llx_societe.rowid = llx_societe_partners.fx_soc
        left join `category_counterparty` on llx_societe.`categoryofcustomer_id`=category_counterparty.rowid
        left join `llx_c_model` on llx_societe_partners.fx_model = `llx_c_model`.rowid
        left join `llx_c_kind_assets` on `llx_c_model`.`fx_kind_assets` = `llx_c_kind_assets`.`rowid`
        left join llx_c_line_active on `llx_c_line_active`.rowid = `llx_c_kind_assets`.`fx_line_active`
        left join `llx_c_trademark` on `llx_c_model`.`fx_trademark`=`llx_c_trademark`.`rowid`
        left join `llx_c_tare` on `llx_c_tare`.`rowid`=`llx_societe_partners`.`fx_tare`
        where `llx_societe_partners`.`rowid` ='.$_REQUEST['rowid'];
//    die($sql);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $item = $db->fetch_object($res);



    include($_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/' . $conf->theme . '/addpartner.html');
    print "<script>
        $(document).ready(function(){
            $('#percent_payment [value=".$item->percent_payment."]').attr('selected', 'selected');
            $('#guarantee [value=".$item->guarantee."]').attr('selected', 'selected');
        })
    </script>";
    exit();
}elseif($action == 'save'){
    if(empty($_REQUEST['fx_soc'])){
        $partner = new Societe($db);
        $partner->name = $_REQUEST['nom'];
        $partner->categoryofcustomer_id=$_REQUEST['categoryofcustomer'];
        $partner->create($user);
        $_REQUEST['fx_soc']=$partner->id;
    }
//    var_dump($_REQUEST['fx_soc']);
//    die();
    if(empty($_REQUEST['rowid'])){
        $sql = 'insert into llx_societe_partners(socid,fx_soc,fx_model,dCol_ed,fx_measurement,fx_tare,fx_tare_measurement,account_date,
        account_number,discont_from_price,discont_from_units,price,percent_payment,delay,delivery,guarantee,
        present,money_turnover,comment,id_usr)
        values('.$_REQUEST['socid'].','.$_REQUEST['fx_soc'].','.(empty($_REQUEST['model'])?"null":$_REQUEST['model']).','.
            (empty($_REQUEST['dCol_ed'])?"null":$_REQUEST['dCol_ed']).','.
            (empty($_REQUEST['measurement'])?"null":$_REQUEST['measurement']).','.
            (empty($_REQUEST['tare'])?"null":$_REQUEST['tare']).','.
            (empty($_REQUEST['fx_tare_measurement'])?"null":$_REQUEST['fx_tare_measurement']).','.
            (empty($_REQUEST['account_date'])?"null":('"'.$_REQUEST['account_date']).'"').','.
            (empty($_REQUEST['account_number'])?"null":('"'.$_REQUEST['account_number']).'"').','.
            (empty($_REQUEST['discont_from_price'])?"null":('"'.$_REQUEST['discont_from_price']).'"').','.
            (empty($_REQUEST['discont_from_units'])?"null":('"'.$_REQUEST['discont_from_units']).'"').','.
            (empty($_REQUEST['price'])?"null":$_REQUEST['price']).','.
            (empty($_REQUEST['percent_payment'])?"null":$_REQUEST['percent_payment']).', '.
            (empty($_REQUEST['delay'])?"null":('"'.$_REQUEST['delay']).'"').','.
            (empty($_REQUEST['delivery'])?"null":('"'.$_REQUEST['delivery']).'"').', '.
            (empty($_REQUEST['guarantee'])?"null":$_REQUEST['guarantee']).','.
            (empty($_REQUEST['present'])?"null":('"'.$_REQUEST['present']).'"').','.
            (empty($_REQUEST['money_turnover'])?"null":$_REQUEST['money_turnover']).','.
            (empty($_REQUEST['comment'])?"null":('"'.$_REQUEST['comment']).'"').', '.$user->id.')';
    }else{
        $sql = "update llx_societe_partners set
        socid = ".$_REQUEST['socid'].",
        fx_soc= ".(empty($_REQUEST['fx_soc'])?"null":$_REQUEST['fx_soc']).",
        fx_model = ".(empty($_REQUEST['model'])?"null":$_REQUEST['model']).",
        dCol_ed = ".(empty($_REQUEST['dCol_ed'])?"null":$_REQUEST['dCol_ed']).",
        fx_measurement = ".(empty($_REQUEST['measurement'])?"null":$_REQUEST['measurement']).",
        fx_tare = ".(empty($_REQUEST['tare'])?"null":$_REQUEST['tare']).",
        fx_tare_measurement = ".(empty($_REQUEST['fx_tare_measurement'])?"null":$_REQUEST['fx_tare_measurement']).",
        account_date = ".(empty($_REQUEST['account_date'])?"null":('"'.$_REQUEST['account_date']).'"').",
        account_number = ".(empty($_REQUEST['account_number'])?"null":('"'.$_REQUEST['account_number']).'"').",
        discont_from_price = ".(empty($_REQUEST['discont_from_price'])?"null":('"'.$_REQUEST['discont_from_price']).'"').",
        discont_from_units = ".(empty($_REQUEST['discont_from_units'])?"null":('"'.$_REQUEST['discont_from_units']).'"').",
        price = ".(empty($_REQUEST['percent_payment'])?"null":$_REQUEST['percent_payment']).",
        percent_payment = ".(empty($_REQUEST['delay'])?"null":('"'.$_REQUEST['delay']).'"').",
        delay = ".(empty($_REQUEST['delay'])?"null":('"'.$_REQUEST['delay']).'"').",
        delivery= ".(empty($_REQUEST['delivery'])?"null":('"'.$_REQUEST['delivery']).'"').",
        guarantee = ".(empty($_REQUEST['guarantee'])?"null":$_REQUEST['guarantee']).",
        present = ".(empty($_REQUEST['present'])?"null":('"'.$_REQUEST['present']).'"').",
        money_turnover = ".(empty($_REQUEST['money_turnover'])?"null":$_REQUEST['money_turnover']).",
        comment = ".(empty($_REQUEST['comment'])?"null":('"'.$_REQUEST['comment']).'"').",
        id_usr=".$user->id." where rowid=".$_REQUEST['rowid'];
    }
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


$Title = $langs->trans("PartnersOfCustomer");
llxHeader('',$Title,$help_url);
print_fiche_titre($Title);
$object = new Societe($db);
$object->fetch($socid);
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
if($user->respon_alias == 'sale')
    print '<div class="inline-block tabsElem">
                    <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/economin_indicator.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$_REQUEST['socid'].'">'.$langs->trans('EconomicData').'</a>
                </div>';
elseif($user->respon_alias == 'purchase') {
    print '<div class="inline-block tabsElem">
                    <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/economin_indicator.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$_REQUEST['socid'].'">'.$langs->trans('LineActive').'</a>
                </div>';
}
print '<div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/finance.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&socid='.$_REQUEST['socid'].'">'.$langs->trans('FinanceAndDetails').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tabactive tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/partners.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&socid='.$_REQUEST['socid'].'">'.$langs->trans('PartnersOfCustomer').'</a>
            </div>
        </div>';
$table = ShowTable();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/societepartners.html');
exit();
function ShowTable()
{
    global $db, $langs, $conf;
    $sql = 'select `llx_societe_partners`.rowid, llx_user.lastname, `llx_societe_partners`.dtChange,
        category_counterparty.name catname, llx_societe.nom contragentname, llx_societe.address,
        llx_c_line_active.line, `llx_c_kind_assets`.`kind_assets`, `llx_c_trademark`.`trademark`, `llx_c_model`.`model`,
        `llx_societe_partners`.`dCol_ed`, count_mes.name as count_mes, `llx_c_tare`.`name` tare,
        tare_mes.name as tare_mes, account_date,account_number,discont_from_price,
        discont_from_units,price,percent_payment,delay,delivery,guarantee,present,money_turnover,`comment`
        from `llx_societe_partners`
        left join llx_user on llx_user.rowid = llx_societe_partners.id_usr
        left join llx_societe on llx_societe.rowid = llx_societe_partners.fx_soc
        left join `category_counterparty` on llx_societe.`categoryofcustomer_id`=category_counterparty.rowid
        left join `llx_c_model` on llx_societe_partners.fx_model = `llx_c_model`.rowid
        left join `llx_c_kind_assets` on `llx_c_model`.`fx_kind_assets` = `llx_c_kind_assets`.`rowid`
        left join llx_c_line_active on `llx_c_line_active`.rowid = `llx_c_kind_assets`.`fx_line_active`
        left join `llx_c_trademark` on `llx_c_model`.`fx_trademark`=`llx_c_trademark`.`rowid`
        left join `llx_c_tare` on `llx_c_tare`.`rowid`=`llx_societe_partners`.`fx_tare`
        left join `llx_c_measurement` count_mes on count_mes.`rowid` = `llx_societe_partners`.`fx_measurement`
        left join `llx_c_measurement` tare_mes on tare_mes.`rowid` = `llx_societe_partners`.`fx_tare_measurement`
        where socid = '.$_REQUEST['socid'].'
        order by rowid';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '<tbody id="reference_body">';
    $num = 0;
    while($obj = $db->fetch_object($res)){
        $date = new DateTime($obj->dtChange);
        $out.='<tr id="'.$obj->rowid.'" class = "'.(fmod($num++, 2)==0?'impair':'pair').' small_size">
        <td>'.$obj->lastname.'</td>
        <td title="'.$date->format('d.m.y').'">'.$date->format('d.m').'</td>
        <td>'.$obj->catname.'</td>
        <td>'.$obj->contragentname.'</td>
        <td>'.$obj->address.'</td>
        <td>'.$obj->line.'</td>
        <td>'.$obj->kind_assets.'</td>
        <td>'.$obj->trademark.'</td>
        <td>'.$obj->model.'</td>
        <td>'.$obj->dCol_ed.'</td>
        <td>'.$obj->count_mes.'</td>
        <td>'.$obj->tare.'</td>
        <td>'.$obj->tare_mes.'</td>
        <td>&nbsp;</td>
        <td>'.$obj->account_date.'</td>
        <td>'.$obj->account_number.'</td>
        <td>'.$obj->discont_from_price.'</td>
        <td>'.$obj->discont_from_units.'</td>
        <td>'.$obj->price.'</td>
        <td>'.$obj->percent_payment.'</td>
        <td>'.$obj->delay.'</td>
        <td>'.$obj->delivery.'</td>
        <td>'.$obj->guarantee.'</td>
        <td>'.$obj->present.'</td>
        <td>'.$obj->money_turnover.'</td>
        <td>'.$obj->comment.'</td>
            <td style="width: 20px" align="left">
                <img  id="img_'. $obj->rowid.'" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/edit.png" title="'.$langs->trans('Edit').'" style="vertical-align: middle" onclick="edit_item(' . $obj->rowid . ');">
            </td>
        ';
    }
    $out .= '</tbody>';
    return $out;
}