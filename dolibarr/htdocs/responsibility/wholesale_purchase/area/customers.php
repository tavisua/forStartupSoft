<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 07.11.2015
 * Time: 11:32
 */
 $region_id = $_SESSION['region_id'];
$search = explode(',',$_GET['search']);
$search_array = array();
foreach($search as $elem) {
    $tmp = explode('=>', $elem);
    $search_array[$tmp[0]]=$tmp[1];
}
$page = isset($_GET['page'])?$_GET['page']:1;
$per_page = isset($_GET['per_page'])?$_GET['per_page']:30;
$sql = "select distinct case when `fx_category_counterparty` is null then other_category else fx_category_counterparty end fx_category_counterparty from `responsibility_param` where `fx_responsibility` =".$respon_id;
$res = $db->query($sql);
$category = array();
if($db->num_rows($res)>0)
    while($obj = $db->fetch_object($res))
        $category[]=$obj->fx_category_counterparty;
//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//die();

if(empty($_GET['viewname']))
    $name = "concat(`llx_societe`.`nom`,' ',case when `formofgavernment`.`name` is null then '' else `formofgavernment`.`name` end)";
elseif ($_GET['viewname'] == "reverse")
    $name = "concat(case when `formofgavernment`.`name` is null then '' else `formofgavernment`.`name` end,' ',`llx_societe`.`nom`)";

$sql = "select distinct `llx_societe`.rowid, $name nom,
`llx_societe`.`town`, round(`llx_societe_classificator`.`value`,0) as width, `llx_societe`.`remark`, ' ' deficit,
' ' task,' ' lastdate, ' ' lastdatecomerc, ' ' futuredatecomerc, ' ' exec_time, ' ' lastdateservice,
' ' futuredateservice, ' ' lastdateaccounts, ' ' futuredateaccounts, ' ' lastdatementor, ' ' futuredatementor
from `llx_societe` left join `category_counterparty` on `llx_societe`.`categoryofcustomer_id` = `category_counterparty`.rowid
left join `formofgavernment` on `llx_societe`.`formofgoverment_id` = `formofgavernment`.rowid
left join `llx_societe_classificator` on `llx_societe`.rowid = `llx_societe_classificator`.`soc_id`
left join `llx_societe_lineactive` on `llx_societe_lineactive`.fk_soc = `llx_societe`.rowid
where 1 and `llx_societe`.active = 1 and `llx_societe`.`categoryofcustomer_id` in (".(count($category)>0?implode(',',$category):'0').") and (`llx_societe`. fk_user_creat = ".$id_usr." or `llx_societe_lineactive`.`fk_lineactive` in (".implode(',', $user->getLineActive($id_usr))."))";


$sql_count = 'select count(*) iCount from
(select distinct `llx_societe`.*  from `llx_societe`
left join `llx_societe_lineactive` on `llx_societe_lineactive`.fk_soc = `llx_societe`.rowid
where 1 and `llx_societe`.active = 1 and `llx_societe`.`categoryofcustomer_id` in ('.(count($category)>0?implode(',',$category):'0').') and (`llx_societe`. fk_user_creat = '.$id_usr.' or `llx_societe_lineactive`.`fk_lineactive` in ('.implode(',', $user->getLineActive($id_usr)).')) ';

//    $tmp = 'and `llx_societe`.`categoryofcustomer_id` in
//(select responsibility_param.fx_category_counterparty from responsibility_param  where fx_responsibility = '.$respon_id.')';
//    $sql.=$tmp;
//    $sql_count.=$tmp;

    $sql_count.=' and `llx_societe`.active = 1 ';

//echo '<pre>';
//var_dump($id_usr);
//echo '</pre>';

if(isset($_REQUEST['lineactive'])&& !empty($_REQUEST['lineactive']) && $_REQUEST['lineactive'] == -1) {
    $tmp = 'select `llx_societe`.rowid from `llx_societe`
        left join `llx_societe_lineactive` on `llx_societe_lineactive`.fk_soc = `llx_societe`.rowid
        left join `llx_societe_classificator` on `llx_societe`.rowid = `llx_societe_classificator`.`soc_id`
        where 1  and `llx_societe`.active = 1
        and (`llx_societe`. fk_user_creat = '.$id_usr.' and (`llx_societe`.`categoryofcustomer_id` in ('.(count($category)>0?implode(',',$category):'0').') or `llx_societe_lineactive`.`fk_lineactive` in ('.implode(',', $user->getLineActive($id_usr)).')))
        order by round(`llx_societe_classificator`.`value`,0) desc, nom';
//echo '<pre>';
//var_dump($tmp);
//echo '</pre>';
//die();
    $res = $db->query($tmp);
    $socID = array('0');
    if($db->num_rows($res)>0)
        while($obj = $db->fetch_object($res)){
            $socID[]=$obj->rowid;
        }

//    $tmp = ' and (`llx_societe`. fk_user_creat = '.$id_usr.' or `llx_societe_lineactive`.`fk_lineactive` in ('.implode(',', $user->getLineActive()).'))';
    $tmp = ' and `llx_societe`.rowid in ('.implode(',',$socID).')';
    $sql.=$tmp;
}
if(isset($_REQUEST['filter'])&&!empty($_REQUEST['filter'])||isset($_REQUEST['lineactive'])&& !empty($_REQUEST['lineactive'])){
    if(isset($_REQUEST['filter'])&&!empty($_REQUEST['filter'])) {
        $phone_number = fPrepPhoneFilter($_REQUEST['filter']);
        $sql_cat = "select fx_category_counterparty from responsibility_param where `fx_responsibility` = ".$respon_id." and fx_category_counterparty is not null";
        $res_cat = $db->query($sql_cat);
        $cat_ID = array(0);
        while($obj = $db->fetch_object($res_cat)){
            if(!in_array($obj->fx_category_counterparty, $cat_ID))
                $cat_ID[] = $obj->fx_category_counterparty;
        }
        $sql_filter = "select llx_societe.rowid from llx_societe
            left join `llx_societe_contact` on `llx_societe_contact`.`socid`=`llx_societe`.`rowid`
            where 1 and `llx_societe`.`categoryofcustomer_id` in (".implode(',', $cat_ID).") and `llx_societe`.`nom`  like '%" . $_REQUEST['filter'] . "%'
            or `llx_societe_contact`.`lastname`  like '%" . $_REQUEST['filter'] . "%'
            or `llx_societe_contact`.`firstname`  like '%" . $_REQUEST['filter'] . "%'
            or `llx_societe_contact`.`subdivision`  like '%" . $_REQUEST['filter'] . "%'
            or `llx_societe_contact`.`email1`  like '%" . $_REQUEST['filter'] . "%'
            or `llx_societe_contact`.`email2`  like '%" . $_REQUEST['filter'] . "%'";
        if (strlen($phone_number) > 0) {
            $sql_filter .= " or `llx_societe_contact` . `mobile_phone1`  like '%" . $phone_number . "%'
        or `llx_societe_contact` . `mobile_phone2`  like '%" . $phone_number . "%' ";
        }
        $sql_filter .= "or `llx_societe_contact`.`skype`  like '%" . $_REQUEST['filter'] . "%'";
        $res = $db->query($sql_filter);
        if (!$res)
            dol_print_error($db);
        $filterid = array();
        if ($db->num_rows($res))
            while ($obj = $db->fetch_object($res)) {
                if (!in_array($obj->rowid, $filterid))
                    $filterid[] = $obj->rowid;
            }
        if(count($filterid) == 0) {
            ClearFilterMessage();
        }
    }
//    echo '<pre>';
//    var_dump($filterid);
//    echo '</pre>';
//    die();
    if(isset($_REQUEST['lineactive'])&& !empty($_REQUEST['lineactive']) && $_REQUEST['lineactive'] != -1){
        $kind = (isset($_REQUEST['kind'])&& !empty($_REQUEST['kind']))?$_REQUEST['kind']:'lineactive';
        switch($kind) {
            case 'lineactive': {
                $sql_filter = 'select `llx_societe`.`rowid` from `llx_societe_lineactive`
                    inner join `llx_societe` on `llx_societe_lineactive`.`fk_soc`=`llx_societe`.`rowid`
                    inner join (select ' . $_REQUEST['lineactive'] . ' as category_id
                      union
                      select category_id from `oc_category`
                      where parent_id=' . $_REQUEST['lineactive'] . '
                      union
                      select parent_id from `oc_category`
                      where category_id=' . $_REQUEST['lineactive'] . ') lineactive on lineactive.category_id=`llx_societe_lineactive`.`fk_lineactive`
                    where 1';
            }break;
            case 'category':{
                if(is_numeric($_REQUEST['lineactive'])){
                    $sql_filter = 'select `llx_societe`.`rowid` from `llx_societe` where fk_user_creat = '.$id_usr.' and categoryofcustomer_id = '.$_REQUEST['lineactive'].' and active = 1';
                }
            }break;
        }
//        var_dump($sql_filter);
//        die();
        $res = $db->query($sql_filter);
        if (!$res)
            dol_print_error($db);
        $filterid = array();
        if ($db->num_rows($res))
            while ($obj = $db->fetch_object($res)) {
                if (!in_array($obj->rowid, $filterid))
                    $filterid[] = $obj->rowid;
            }
    }
    if(count($filterid)) {
        $sql .= ' and `llx_societe`.`rowid` in (' . implode(',', $filterid) . ') ';
        $sql_count .= ' and `llx_societe`.`rowid` in (' . implode(',', $filterid) . ')';
    }else{
        if(isset($_REQUEST['lineactive'])&& !empty($_REQUEST['lineactive']) && $_REQUEST['lineactive'] != -1) {
            $sql .= ' and `llx_societe`.`rowid` in (0) ';
            $sql_count .= ' and `llx_societe`.`rowid` in (0)';
        }
    }
}
$sql_count.=') societe';
$sql .= ' order by width desc, nom';
$sql .= ' limit '.($page-1)*$per_page.','.$per_page;

//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//die();

$res = $db->query($sql_count);
if(!$res)
    dol_print_error($db);
$count = $db->fetch_object($res);


$total = ceil($count->iCount/$per_page);

$TableParam = array();
$ColParam['title']='';
$ColParam['width']='178';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['substr']='20';
$TableParam[]=$ColParam;
unset($ColParam['substr']);

$ColParam['title']='';
$ColParam['width']='98';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['substr']='10';
$TableParam[]=$ColParam;


$ColParam['title']='';
$ColParam['width']='50';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;


$ColParam['title']='';
$ColParam['width']='129';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['substr']='15';
$TableParam[]=$ColParam;
unset($ColParam['substr']);

$ColParam['title']='';
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='80';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='74';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='74';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='40';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='74';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='74';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='74';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='74';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='75';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='75';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='50';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename = "`llx_societe`";
//include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
//$db_mysql = new dbBuilder();

$table = fShowTable($TableParam, $sql, "'" . $tablename . "'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder'], $readonly = array(-1), false);

//$row = $db_mysql->fShowTable($TableParam, $sql, "'" . $tablename . "'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder'], $readonly = array(-1), false);
if(empty($_GET['viewname']))
    $kind_view = '</br><span style="padding-top: 30px"><button onclick="ChangeViewNameOption();">Назва <img src="/dolibarr/htdocs/theme/eldy/img/replace.png">&nbsp;&nbsp;&nbsp;ФП&nbsp;&nbsp;&nbsp;</button></span>';
elseif ($_GET['viewname'] == 'reverse')
    $kind_view = '</br><span style="padding-top: 30px"><button onclick="ChangeViewNameOption();">&nbsp;&nbsp;&nbsp;ФП&nbsp;&nbsp;&nbsp; <img src="/dolibarr/htdocs/theme/eldy/img/replace.png"> Назва</button></span>';

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/wholesale_purchase/area/customers.html');
$prev_form = "<a href='#x' class='overlay' id='peview_form'></a>
                     <div class='popup' style='width: 300px;height: 150px'>
                     <textarea readonly id='prev_form' style='width: 100%;height: 100%;resize: none'></textarea>
                     <a class='close' title='Закрыть' href='#close'></a>
                     </div>";
print $prev_form;

return;

function ClearFilterMessage(){
        echo '<div style="height: 150px"></div>';
        print '<form id="setfilter" action="" method="get">
                <input type="hidden" name="mainmenu" value="'.$_REQUEST["mainmenu"].'">
                <input type="hidden" name="idmenu" value="'.$_REQUEST["idmenu"].'">
                <input type="hidden" name="filter" value="" id="filter" size="45">
        </form>';
        print "
        <script>
        function clearfilter(){
            $('#setfilter').submit();
        }
        </script>";
        die('<span style="font-size: 20px">Не знайдено жодного господарства. Натисніть кнопку <button style="height: 25px" onclick="clearfilter();">Зняти фільтр</button></span>');
}

function fPrepPhoneFilter($phonenumber){
    //Clear notnumeric symbol
    for($i = 0; $i<strlen($phonenumber); $i++){
        if(!is_numeric(substr($phonenumber, $i,1))) {
            if($i == 0)
                $tmp = substr($phonenumber, $i+1) ;
            else
                $tmp = substr($phonenumber, 0, $i).substr($phonenumber, $i+1) ;
            $phonenumber = $tmp;
            $i--;
        }
    }
    $tmp='';
    for($i = 0; $i<strlen($phonenumber); $i++){
        if($i+1==strlen($phonenumber))
            $tmp.=substr($phonenumber, $i, 1);
        else
            $tmp.=substr($phonenumber, $i, 1).'%';
    }
    $phonenumber = $tmp;

    return $phonenumber;
}

function fShowTable($title = array(), $sql, $tablename, $theme, $sortfield='', $sortorder='', $readonly = array(), $showtitle=true){
    global $user, $conf, $langs, $db;


    if(empty($sortorder))
        $result = $db->query($sql);
    else{
//            echo '<pre>';
//            var_dump($sql);
//            echo '</pre>';
//            die();

        $result = $db->query($sql.' limit 1');

        $fields = $result->fetch_fields();
        $num_col=0;
        for($i=1;$i<count($fields);$i++){
            if($fields[$i]->name != 'rowid' && !isset($title[$num_col]['hidden'])){
//                var_dump($num_col.' '.$fields[$i]->name.'</br>');
                if($num_col == $sortfield) {
                    if (substr($fields[$i]->name, 0, 2) != 's_') {
                        $t_name = str_replace("'", '', $tablename);
                        $fieldname = $fields[$i]->name;
                    } elseif (substr($fields[$i]->name, 0, 2) == 's_') {
                        $t_name = substr($fields[$i]->name, 2, strpos($fields[$i]->name, '_', 3)-2);//
                        $fieldname = substr($fields[$i]->name, strpos($fields[$i]->name, '_', 3)+1);
                    }
                    if(count($readonly) == 0)
                        $result = $db->query(substr($sql, 0, strpos($sql, 'order by')).' order by trim(`'.$t_name.'`.`'.$fieldname.'`) '.$sortorder);
                    else {
                        $result = $db->query(substr($sql, 0, strpos($sql, 'order by')) . ' order by trim(`' . $fieldname . '`) ' . $sortorder);
                    }
                    break;
                }
            }
            $num_col++;
        }
    }
    if($db->num_rows($result)==0)
        ClearFilterMessage();
    $rowidList=array();
    while($obj = $db->fetch_object($result)){
        $rowidList[]=$obj->rowid;
    }
    mysqli_data_seek($result, 0);
    $actionfields = array('futuredatecomerc'=>'wholesale_purchase', 'lastdatecomerc'=>'wholesale_purchase',  'lastdateservice'=>'service', 'lastdateaccounts'=>'accounts',  'lastdatementor'=>'mentor');
    if(!$result)return;
    $page = isset($_GET['page'])?$_GET['page']:1;
    $per_page = isset($_GET['per_page'])?$_GET['per_page']:30;

    $lastaction = array();
//    $sql = "select `llx_societe`.rowid, max(`llx_societe_action`.`dtChange`) dtChange, `responsibility`.`alias`
//    from `llx_societe`
//    left join `llx_societe_classificator` on `llx_societe`.rowid = `llx_societe_classificator`.`soc_id`
//    left join `llx_actioncomm` on `llx_actioncomm`.`fk_soc`= `llx_societe`.rowid
//    inner join (select code, libelle label from `llx_c_actioncomm` where active = 1 and (type = 'system' or type = 'user')) TypeCode on TypeCode.code = `llx_actioncomm`.code
//    left join `llx_societe_action` on `llx_societe_action`.`action_id` = `llx_actioncomm`.`id`
//    inner join `llx_user` on `llx_societe_action`.id_usr = `llx_user`.`rowid`
//    left join `responsibility` on `responsibility`.`rowid`=`llx_user`.`respon_id`
//    where `llx_societe`.active = 1
//    and `llx_societe_action`.active = 1
//    and `llx_actioncomm`.active = 1
//    group by `llx_societe`.rowid, `responsibility`.`alias` ";
//    $sql .= ' limit '.($page-1)*$per_page.','.$per_page;
if(count($rowidList)>0) {

        $sql = "select `llx_societe_action`.`socid` as rowid, max(`llx_societe_action`.`dtChange`) dtChange, `responsibility`.`alias`  from `llx_societe_action`
        left join `llx_user` on `llx_societe_action`.id_usr = `llx_user`.`rowid`
        left join `responsibility` on `responsibility`.`rowid`=`llx_user`.`respon_id`
        where 1 ";
        $sql .= " and `llx_societe_action`.`socid` in (" . implode(',', $rowidList) . ")";
        $sql .= "    and `llx_societe_action`.active = 1
        group by `llx_societe_action`.`socid`, `responsibility`.`alias`;";
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
        $res = $db->query($sql);
        if (!$res) {
            dol_print_error($db);
        }
        if ($db->num_rows($res) > 0) {
            while ($row = $db->fetch_object($res)) {
                if (!isset($lastaction[$row->rowid . $row->alias])) {
                    $date = new DateTime($row->dtChange);
                    $lastaction[$row->rowid . $row->alias] = $date->format('d.m.y');
                }
            }
        }
    }
//  die($sql);
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    if($db->num_rows($res)>0) {
        while ($row = $db->fetch_object($res)){
            if(!isset($lastaction[$row->rowid.$row->alias])) {
                $date = new DateTime($row->dtChange);
                $lastaction[$row->rowid . $row->alias] = $date->format('d.m.y');
            }
        }
    }

    $futureaction = array();
//    $sql = "select `llx_societe`.rowid, llx_actioncomm.datep, `responsibility`.`alias`
//        from `llx_societe`
//        left join `llx_societe_classificator` on `llx_societe`.rowid = `llx_societe_classificator`.`soc_id`
//        left join `llx_actioncomm` on `llx_actioncomm`.`fk_soc`= `llx_societe`.rowid
//        inner join (select code, libelle label from `llx_c_actioncomm` where active = 1
//        and (type = 'system' or type = 'user')) TypeCode on TypeCode.code = `llx_actioncomm`.code
//        inner join `llx_user` on `llx_actioncomm`.`fk_user_author` = `llx_user`.`rowid`
//        left join `responsibility` on `responsibility`.`rowid`=`llx_user`.`respon_id`
//        where `llx_societe`.active = 1
//        and `llx_actioncomm`.`id` not in (select `llx_societe_action`.`action_id` from llx_societe_action)
//        and `llx_actioncomm`.active = 1
//        limit 0,30";
    if(count($rowidList)>0) {
        $sql = "select `llx_actioncomm`.`fk_soc` rowid, llx_actioncomm.datep, `responsibility`.`alias` from `llx_actioncomm`
        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm`=`llx_actioncomm`.id
        inner join (select code, libelle label from `llx_c_actioncomm` where active = 1
        and (type = 'system' or type = 'user')) TypeCode on TypeCode.code = `llx_actioncomm`.code
        inner join `llx_user` on `llx_actioncomm`.`fk_user_action` = `llx_user`.`rowid`
        left join `responsibility` on `responsibility`.`rowid`=`llx_user`.`respon_id`
        where 1
        and `llx_actioncomm`.`fk_soc` in (" . implode(',', $rowidList) . ")
        and `llx_actioncomm`.`active` = 1
        and `llx_actioncomm`.`id` not in (select `llx_societe_action`.`action_id` from llx_societe_action where action_id is not null)";

        $res = $db->query($sql);
        if (!$res) {
            dol_print_error($db);
        }
        if ($db->num_rows($res) > 0) {
            while ($row = $db->fetch_object($res)) {
                if (!isset($futureaction[$row->rowid . $row->alias])) {
                    $date = new DateTime($row->datep);
                    $futureaction[$row->rowid . $row->alias] = $date->format('d.m.y');
                }
            }
        }
    }
//    echo '<pre>';
//    var_dump($futureaction);
//    echo '</pre>';
//    die();
    $fields = $result->fetch_fields();
//        var_dump($showtitle);
//        die();
    if($showtitle) {
        $table = '<table class="scrolling-table" >' . "\r\n";
        $table .= '<thead >' . "\r\n";
        $table .= '<tr class="liste_titre" id="reference_title">' . "\r\n";
    }
    $count = 0;
    $widthtable = 0;
    $hiddenfield = "''";
    $sendtable = "''";
    $num_col = 0;
    $additionparam = false;
    $colindex = 0;
    foreach ($title as $column) {
        if (!isset($column['hidden'])) {
            if($showtitle) {
                $table .= '<th id = "th' . $colindex++ . '" class="liste_titre" ';
                $table .= $column['width'] <> '' ? ('style="width:' . $column['width'] . 'px"') : ('auto');//ширина
                $table .= $column['align'] <> '' ? ('align=' . $column['align'] . '"') : (' ');//выравнивание
                $table .= $column['class'] <> '' ? ('class=' . $column['class'] . '"') : (' ');//класс
                $table .= '>' . $column['title'] . '
                     <span class="nowrap">
                    <a href="' . $_SERVER['PHP_SELF'] . '?mainmenu=tools&sortfield=' . $num_col . '&sortorder=asc">';
                if (isset($_REQUEST['sortfield']) && $_REQUEST['sortfield'] == $num_col && isset($_REQUEST['sortorder']) && $_REQUEST['sortorder'] == "asc")
                    $table .= '<img class="imgup" border="0" title="Я-A" alt="" src="/dolibarr/htdocs/theme/' . $conf->theme . '/img/1uparrow_selected.png">';
                else
                    $table .= '<img class="imgup" border="0" title="Я-A" alt="" src="/dolibarr/htdocs/theme/' . $conf->theme . '/img/1uparrow.png">';
                $table .= '</a>
                    <a href="' . $_SERVER['PHP_SELF'] . '?mainmenu=tools&sortfield=' . $num_col . '&sortorder=desc">';
                if (isset($_REQUEST['sortfield']) && $_REQUEST['sortfield'] == $num_col && isset($_REQUEST['sortorder']) && $_REQUEST['sortorder'] == "desc")
                    $table .= '<img class="imgdown" border="0" title="A-Я" alt="" src="/dolibarr/htdocs/theme/' . $conf->theme . '/img/1downarrow_selected.png">';
                else
                    $table .= '<img class="imgdown" border="0" title="A-Я" alt="" src="/dolibarr/htdocs/theme/' . $conf->theme . '/img/1downarrow.png">';
                $table .= '</a>
                    </span>
                    </th>';
            }
            $widthtable += $column['width']+3;
        } else {
            $hiddenfield = $column['detailfield'];
            $sendtable = $column['hidden'];
            $additionparam = true;
        }
        $num_col++;
    }
    $widthtable += 55;

    if($showtitle) {
        if (count($readonly) == 0) {
            $table .= '<th width="20px">
                <img class="boxhandle hideonsmartphone" border="0" style="cursor:move;" title="" alt="" src="/dolibarr/htdocs/theme/' . $theme . '/img/grip_title.png">' . "\r\n";
            $table .= '<input id="boxlabelentry18" type="hidden" value="">
                </th>' . "\r\n";
        }
        $table .= '</tr>'."\r\n";
        $table .= '</thead>' . "\r\n";
//        echo '<pre>';
//        var_dump($title);
//        echo '</pre>';
//        die();
    }
    $col_width = array();
    foreach($title as $column){
        $col_width[]=$column['width'];
    }

    $table .= '<tbody id="reference_body" style="width: '.(count($readonly)==0?$widthtable:$widthtable-40).'">'."\r\n";




    $count = 0;
    if(count($readonly)==0) {
        $create_edit_form = false;
        $edit_form = "<a href='#x' class='overlay' id='editor'></a>
                     <div class='popup'>
                     <form>
                     <input type='hidden' id='user_id' name='user_id' value=" . $id_usr . ">
                     <input type='hidden' id='edit_rowid' name='rowid' value='0'>
                     <table id='edit_table'>
                     <tbody>";
    }
    //Если запрос вернул пустой результат, дорисую одну строку
    if ($result->num_rows == 0) {
        $class = fmod($count, 2) != 1 ? ("impair") : ("pair");
        $table .= "<tr id = 0 class='" . $class . "'>\r\n";
        $num_col = 0;
        for ($i = 0; $i <= $result->field_count; $i++) {
            if ($fields[$i]->name != 'rowid') {
                if ($result->field_count != $i) {
                    $table .= '<td id="0" >&nbsp;</td>';
                    if(count($readonly)==0)
                        $edit_form .= $this->fBuildEditForm($title[$num_col], $fields[$i], $theme, $tablename);
                } elseif($showtitle)
                    $table .= '<td id="0" style="width: 20px"></td>';
                $num_col++;
            }
        }

        if(count($readonly)==0) {
            $param = "'',''";
            $edit_form .= "    </tbody>
                                    </table>
                           </form>
                        <a class='close' title='Закрыть' href='#close'></a>


                        <button onclick=save_item(".$tablename.",".$param."); >Сохранить</button>
                        <button onclick='close_form();'>Закрыть</button>
                        </div>";
        }
    }

    while($row = $result->fetch_assoc()) {
        $count++;
        $class = fmod($count,2)==1?("impair"):("pair");
        $table .= "<tr id = tr".$row['rowid']." class='".$class."'>\r\n";
//            $table .= "<tr id = tr".$row['rowid']." class='".$class."'>\r\n";
//            $table .= "<tr id = $count class=".fmod($count,2)==1?('impair'):('pair').">\r\n";
        $id = $row['rowid'];
//            $table .= '<td >'.$class.'</td>';
//            echo '<pre>';
//            var_dump($fields);
//            echo '</pre>';
//            die();
//            echo '</br>';
        $num_col = 0;
        $prev_col=array('nom','town','remark');
        foreach($row as $cell=>$value){
            $col_name = "'".$fields[$num_col]->name."'";
            if($cell != 'rowid') {
                if(!$create_edit_form && count($readonly)==0)//Формирую форму для редактирования
//                    $edit_form.=$this->fBuildEditForm($title[$num_col-1], $fields[$num_col], $theme, $tablename);
                    var_dump($title[$num_col-1]['title'].' '.$cell.' '.!isset($title[$num_col-1]['hidden']).'</br>');
                if(!isset($title[$num_col-1]['hidden'])) {
//                        echo'<pre>';
//                        var_dump($cell);
//                        echo'</pre>';

                    if ($fields[$num_col]->type == 16) {
                        if(count($readonly)==0) {
                            if ($value == '1') {
                                $table .= '<td class = "switch" id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width:'.($col_width[$num_col-1]+2).'px" ><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_on.png" onclick="change_switch(' . $row['rowid'] . ', ' . $tablename . ', ' . $col_name . ');" > </td>';
                            } else {
                                $table .= '<td class = "switch" id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width:'.($col_width[$num_col-1]+2).'px" ><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_off.png" onclick="change_switch(' . $row['rowid'] . ', ' . $tablename . ', ' . $col_name . ');"> </td>';
                            }
                        }else{
                            if(in_array($row['rowid'], $readonly)){
                                $table .= '<td class = "switch" id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width:'.($col_width[$num_col-1]+2).'px"><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_on.png"> </td>';
                            }else{
                                $table .= '<td class = "switch" id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width:'.($col_width[$num_col-1]+2).'px"><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_off.png"> </td>';
                            }
                        }
                    } else {
                        if (substr($fields[$num_col]->name, 0, 2) != 's_') {
                            $full_text='';
                            if(mb_strlen(trim($value))>0) {
                                $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width:' . ($col_width[$num_col - 1] + 2) . 'px;">';
                                if(!isset($title[$num_col - 1]['substr'])||mb_strlen(trim($value), 'UTF-8')<=$title[$num_col - 1]['substr'])
                                    $table .= trim($langs->trans($value));
                                else {

                                    $obj="'".$row['rowid'] . $fields[$num_col]->name."'";
                                    $table .= mb_substr(trim($value), 0, $title[$num_col - 1]['substr'], 'UTF-8') . '...';
//                                    $table .= mb_strlen(trim($value), 'UTF-8').'%%%'.trim($value);
                                    $table .='<img id="prev' . $row['rowid'] . $fields[$num_col]->name . '" onclick="preview(' . $obj . ');" style="vertical-align: middle" title="Передивитись" src="/dolibarr/htdocs/theme/eldy/img/object-more.png">';
                                }
                                $table .='</td>';
                                $full_text = trim($value);
                            }else {
//                                if($row['rowid'] == 3582){
//                                    echo '<pre>';
//                                    var_dump($actionfields);
//                                    echo '</pre>';
//                                    die('test');
//                                }
                                if(isset($actionfields[$fields[$num_col]->name])){
                                    $alias = $actionfields[$fields[$num_col]->name];
                                    $full_text = '';
                                    switch($fields[$num_col]->name){
                                        case 'lastdatecomerc':{
                                            $full_text = !isset($lastaction[$row['rowid'].$alias]) ?
                                                '<img src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/object_action.png">' : $lastaction[$row['rowid'].$alias];
                                        }break;
                                        case 'futuredatecomerc':{
                                            $full_text = !isset($futureaction[$row['rowid'].$alias]) ?
                                                '<img src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/object_action.png">' : $futureaction[$row['rowid'].$alias];
                                        }
                                    }

                                    $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '"  style="width:' . ($col_width[$num_col - 1] + 2) . 'px; text-align: center;"><a href="../sale/action.php?socid=' . $row['rowid'] . '&idmenu=10425&mainmenu=area">' . ($full_text) . '</a> </td>';
                                }else{
                                    $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '"  style="width:' . ($col_width[$num_col - 1] + 2) . 'px; text-align: center;"> </td>';
                                }
                            }

                            if(in_array(trim($fields[$num_col]->name), $prev_col))
                                $table .='<td style="display: none" id="full'.$row['rowid'] . $fields[$num_col]->name.'">'.$full_text.'</td>';
                        }
                        else {

                            if (substr($fields[$num_col]->name, 0, 6) == 's_llx_') {
                                $stpos = 7;
                            } else
                                $stpos = 3;
                            $s_table = substr($fields[$num_col]->name, 2, strpos($fields[$num_col]->name, '_', $stpos) - 2);
                            $s_fieldname = substr($fields[$num_col]->name, strpos($fields[$num_col]->name, '_', $stpos) + 1);

                            $selectlist = substr($this->selectlist['edit_' . $s_table . '_' . $s_fieldname], 0, strpos($this->selectlist['edit_' . $s_table . '_' . $s_fieldname], $value) - 1) . ' selected = "selected" ' . substr($this->selectlist['edit_' . $s_table . '_' . $s_fieldname], strpos($this->selectlist['edit_' . $s_table . '_' . $s_fieldname], $value) - 1);

                            $selectlist = str_replace('class="edit_' . substr($fields[$num_col]->name, 2) . '"', '', $selectlist);

                            if (isset($title[$num_col - 1]["detailfield"])) {
                                $selectlist = str_replace('id="edit_' . substr($fields[$num_col]->name, 2) . '"', 'id="select' . $row['rowid'] . $title[$num_col - 1]["detailfield"] . '"', $selectlist);
                                $detailfield = "'" . $title[$num_col - 1]["detailfield"] . "'";
                                $selectlist = str_replace('<select', '<select onChange="change_select(' . $row['rowid'] . ', ' . $tablename . ', ' . $detailfield . ');"', $selectlist);
                            }
//                            echo '<pre>';
//                            var_dump(htmlspecialchars($selectlist));
//                            echo '</pre>';
//                            die();
                            $table .= '<td  id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width:'.($col_width[$num_col-1]+2).'px" >' . $selectlist . '</td>';
//                            $table .= '<td class = "combobox" id="' . $row['rowid'] . $fields[$num_col]->name . '">' . $value . '</td>';
                        }
                    }
                }
            }
            $num_col++;
        }
        if(!$create_edit_form && count($readonly)==0) {
            $create_edit_form = true;
            $save_item ="save_item(".$tablename.",'".$hiddenfield;
//                var_dump();
//                die();
            $edit_form .='    </table>
                               </form>
                            <a class="close" title="Закрыть" href="#close"></a>
                            </br>';
            if($additionparam) {
                $edit_form .= "<script>
                                var tablename = " . $tablename . ";
                                var fieldname = '" . $hiddenfield . "';
                                var sendtable = '" . $sendtable . "';
                            </script>";

            }else{
                $edit_form .= "<script>
                                var tablename = " . $tablename . ";
                                var fieldname = '';
                                var sendtable = '';
                            </script>";
            }
            $edit_form .= '<button onclick="save_item(tablename, fieldname, sendtable)">Сохранить</button>
                            <button onclick="close_form();">Закрыть</button>
                            </div>';
        }
//
//            var_dump(count($readonly)==0);
//            die();
        $table .= '<td class = "switch" id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width:'.($col_width[$num_col-1]+2).'px" ><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_on.png" onclick="change_switch(' . $row['rowid'] . ');" > </td>';
        if(count($readonly)==0 && $showtitle) {
            $table .= '<td style="width: 20px" align="left">

                <img  id="img_'. $row['rowid'].'" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/edit.png" title="'.$langs->trans('Edit').'" style="vertical-align: middle" onclick="edit_item(' . $row['rowid'] . ');">


                       </td>';
        }

        $table .= '</tr>';
    }
    $table .= '</tbody>'."\r\n";
    $table .= '</table>'."\r\n";
//        if(count($readonly)==0)
//            $table .= '</form>'."\r\n";

    $table .= $edit_form;
    return $table;
}