<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 07.11.2015
 * Time: 11:32
 */
 $region_id = $_SESSION['region_id'];
//var_dump($_SESSION['region_id']);
//die();

$sql = 'select `llx_societe`.rowid, `llx_societe`.nom,
`llx_societe`.`town`, round(`llx_societe_classificator`.`value`,0) as width, `llx_societe`.`remark`, " " deficit,
" " task," " lastdate, " " lastdatecomerc, " " futuredatecomerc, " " lastdateservice,
" " futuredateservice, " " lastdateaccounts, " " futuredateaccounts, " " lastdatementor, " " futuredatementor
from `llx_societe` left join `category_counterparty` on `llx_societe`.`categoryofcustomer_id` = `category_counterparty`.rowid
left join `formofgavernment` on `llx_societe`.`formofgoverment_id` = `formofgavernment`.rowid
left join `llx_societe_classificator` on `llx_societe`.rowid = `llx_societe_classificator`.`soc_id`';
if($region_id != 0) {
    $sql .= 'where `region_id` = ' . $region_id . ' ';
    $sql .= 'and `llx_societe`.`categoryofcustomer_id` in
(select responsibility_param.fx_category_counterparty from responsibility_param  where fx_responsibility = '.$user->respon_id.')';
}else
    $sql .= 'where 1 ';

$sql .= 'order by width desc, nom';
//var_dump($sql);
$TableParam = array();
$ColParam['title']='';
$ColParam['width']='178';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='98';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['substr']='16';
$TableParam[]=$ColParam;
unset($ColParam['substr']);
$ColParam['title']='';
$ColParam['width']='50';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='129';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='98';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='98';
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

$tablename = "`llx_societe`";
//include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
//$db_mysql = new dbBuilder();

$table = fShowTable($TableParam, $sql, "'" . $tablename . "'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder'], $readonly = array(-1), false);

//$row = $db_mysql->fShowTable($TableParam, $sql, "'" . $tablename . "'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder'], $readonly = array(-1), false);

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/customers.html');
return;
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
    $actionfields = array('lastdatecomerc'=>'sale',  'lastdateservice'=>'service', 'lastdateaccounts'=>'accounts',  'lastdatementor'=>'mentor');
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
                     <input type='hidden' id='user_id' name='user_id' value=" . $user->id . ">
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

                            if(strlen(trim($value))>0)
                                $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width:'.($col_width[$num_col-1]+2).'px;">'.
                                    (isset($title[$num_col-1]["substr"])?mb_substr(trim($langs->trans($value)),0,$title[$num_col-1]["substr"], 'UTF-8')
                                        :trim($langs->trans($value))). '</td>';
                            else
                                $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '"  style="width:'.($col_width[$num_col-1]+2).'px; text-align: center;">'.(isset($actionfields[$fields[$num_col]->name])?
                                        '<a href="../'.$actionfields[$fields[$num_col]->name].'/action.php?socid='.$row['rowid'].'&idmenu=10425&mainmenu=area"><img src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/object_action.png"></a>':'').' </td>';
                        }
                        else {

                            if (substr($fields[$num_col]->name, 0, 6) == 's_llx_') {
                                $stpos = 7;
                            } else
                                $stpos = 3;
                            $s_table = substr($fields[$num_col]->name, 2, strpos($fields[$num_col]->name, '_', $stpos) - 2);
                            $s_fieldname = substr($fields[$num_col]->name, strpos($fields[$num_col]->name, '_', $stpos) + 1);

//                                $selectlist = str_replace('selected=\"selected\"', '', $this->selectlist['edit_' . $s_table . '_' . $s_fieldname]);
//                                if('regions' == $s_table) {
//                                    var_dump($selectlist);
//                                    die($value);
//                                }
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