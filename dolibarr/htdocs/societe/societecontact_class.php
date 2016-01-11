<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 19.11.2015
 * Time: 5:43
 */

class societecontact {
    var $rowid;
    var $subdivision;//назва структурного підрозділу
    var $town_id;
    var $location;//назва населеного пункту
    var $post;//посада
    var $SphereOfResponsibility;//сфера відповідальності
    var $lastname;//прізвище
    var $firstname;//ім'я побатькові
    var $work_phone;//робочий телефон
    var $call_work_phone;//дозвіл дзвонити
    var $fax;//факс
    var $call_fax;//дозвіл відправляти повідомлення
    var $mobile_phone1;//мобільний телефон
    var $call_mobile_phone1;//дозвіл дзвонити на мобільний телефон
    var $mobile_phone2;//мобільний телефон
    var $call_mobile_phone2;//дозвіл дзвонити на мобільний телефон
    var $email1;//електронна пошта
    var $send_email1;//дозвіл відправляти повідомлення
    var $email2;//електронна пошта
    var $send_email2;//дозвіл відправляти повідомлення
    var $skype;//логін скайпу
    var $call_skype;//дозвіл дзвонити на скайп
    var $birthdaydate;//дата народження
    var $send_birthdaydate;//дозвіл поздоровляти
    var $socid;


    public function createContact($socid){
//        echo '<pre>';
//        var_dump(empty($this->birthdaydate));
//        echo '</pre>';
//        var_dump(empty($this->lastname)?"null":"'".trim($this->lastname));
//        die();
        global $db, $user;
        $date = new DateTime($this->birthdaydate);
        $sql="insert `llx_societe_contact`(subdivision, post_id, respon_id, lastname, firstname, work_phone,
        call_work_phone,fax,call_fax,mobile_phone1,call_mobile_phone1,mobile_phone2,call_mobile_phone2,
        email1,send_email1,email2,send_email2,skype,call_skype,birthdaydate,send_birthdaydate,socid,active,id_usr)
        values(";
        $sql.= empty($this->subdivision)?"null":"'".$db->escape(trim($this->subdivision))."'";
        $sql.= ", ".$this->post.", ".$this->SphereOfResponsibility.", ";
        $sql.= (empty($this->lastname)?("null"):("'".$db->escape(trim($this->lastname))."'")).", ";
        $sql.= (empty($this->firstname)?("null"):"'".$db->escape(trim($this->firstname))."'").", ";
        $sql.= (empty($this->work_phone)?("null"):"'".$db->escape(trim($this->work_phone))."'").", ";
        $sql.= (empty($this->call_work_phone)?'0':'1').", ";
        $sql.= (empty($this->fax)?"null":"'".trim($this->fax)."'").", ";
        $sql.= (empty($this->call_fax)?'0':'1').", ";
        $sql.= (empty($this->mobile_phone1)?"null":"'".trim($this->mobile_phone1)."'").", ";
        $sql.= (empty($this->call_mobile_phone1)?'0':'1').", ";
        $sql.= (empty($this->mobile_phone2)?"null":"'".trim($this->mobile_phone2)."'").", ";
        $sql.= (empty($this->call_mobile_phone2)?'0':'1').", ";
        $sql.= (empty($this->email1)?"null":"'".trim($this->email1)."'").", ";
        $sql.= (empty($this->send_email1)?'0':'1').", ";
        $sql.= (empty($this->email2)?"null":"'".trim($this->email2)."'").", ";
        $sql.= (empty($this->send_email2)?'0':'1').", ";
        $sql.= (empty($this->skype)?"null":"'".trim($this->skype)."'").", ";
        $sql.= (empty($this->call_skype)?'0':'1').", ";
        $sql.= (empty($this->birthdaydate)?"null":"'".$date->format('Y-m-d H:i:s')."'").", ";
        $sql.= (empty($this->send_birthdaydate)?'0':'1').", ";
        $sql.= $socid.", ";
        $sql.= " 1, ".$user->id.")";


//        die($sql);
        $res = $db->query($sql);
        if(!$res){
            var_dump($sql);
            dol_print_error($db);
        }
    }

    public function selectPost($htmlname='post', $post=0){
        global $db;
        $out = '';
        global $conf, $langs;
        $langs->load("dict");
        $sql = "select rowid, postname name from llx_post where 1";
        $sql .= " and active = 1 order by trim(postname)";

        $resql = $db->query($sql);
        if ($resql) {
            $out .= '<select id = "'.$htmlname.'" class="combobox" name="' . $htmlname . '">';
            $num = $db->num_rows($resql);
            $i = 0;
            if ($num) {
                $country = '';
                $out .= '<option value="0">&nbsp;</option>';
                while ($i < $num) {
                    $obj = $db->fetch_object($resql);
                    if (!empty($post) && $post == $obj->rowid) {
                        $out .= '<option value="' . $obj->rowid . '" selected="selected">' .$obj->name . '</option>';
                    } else {
                        $out .= '<option value="' . $obj->rowid . '">' . $obj->name . '</option>';
                    }
                    $i++;
                }
            }
            $out .= '</select>';
        } else {
            dol_print_error($db);
        }
//        }
        return $out;
    }
    public function selectResponsibility($htmlname='SphereOfResponsibility', $respon_id=0){
        global $db;
        $out = '';
        global $conf, $langs;
        $langs->load("dict");
        $sql = "select rowid, name from responsibility where 1";
        $sql .= " and active = 1 order by trim(name)";

        $resql = $db->query($sql);
        if ($resql) {
            $out .= '<select id = "'.$htmlname.'" class="combobox" name="' . $htmlname . '">';
            $num = $db->num_rows($resql);
            $i = 0;
            if ($num) {
                $country = '';
                $out .= '<option value="0">&nbsp;</option>';
                while ($i < $num) {
                    $obj = $db->fetch_object($resql);
                    if (!empty($respon_id) && $respon_id == $obj->rowid) {
                        $out .= '<option value="' . $obj->rowid . '" selected="selected">' .$obj->name . '</option>';
                    } else {
                        $out .= '<option value="' . $obj->rowid . '">' . $obj->name . '</option>';
                    }
                    $i++;
                }
            }
            $out .= '</select>';
        } else {
            dol_print_error($db);
        }
//        }
        return $out;
    }
    public function fShowTable($title = array(), $sql, $tablename, $theme, $sortfield='', $sortorder='', $readonly = array(), $showtitle=false){
        global $user, $conf, $langs, $db;
//        var_dump($sql);
//        die();
        if(empty($sortorder))
            $result = $db->query($sql);
        else{
//            echo '<pre>';
//            var_dump($sql);
//            echo '</pre>';
//            die();
//            echo '<pre>';
//            var_dump($sql);
//            echo '</pre>';
            $result = $this->mysqli->query($sql.' limit 1');

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
                            $result = $this->mysqli->query(substr($sql, 0, strpos($sql, 'order by')).' order by trim(`'.$t_name.'`.`'.$fieldname.'`) '.$sortorder);
                        else {
                            $result = $this->mysqli->query(substr($sql, 0, strpos($sql, 'order by')) . ' order by trim(`' . $fieldname . '`) ' . $sortorder);
                        }
                        break;
                    }
                }
                $num_col++;
            }
        }

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
                $widthtable += $column['width'];
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
        $table .= '<tbody id="contactlist_body" style="width: '.(count($readonly)==0?$widthtable:$widthtable-40).'">'."\r\n";




        $count = 0;
//        if(count($readonly)==0) {
//            $create_edit_form = false;
//            $edit_form = "<a href='#x' class='overlay' id='editor'></a>
//                     <div class='popup'>
//                     <form>
//                     <input type='hidden' id='user_id' name='user_id' value=" . $user->id . ">
//                     <input type='hidden' id='edit_rowid' name='rowid' value='0'>
//                     <table id='edit_table'>
//                     <tbody>";
//        }
        $ignorfields = array('rowid','call_work_phone','call_fax', 'call_mobile_phone1', 'call_mobile_phone2', 'send_email1', 'send_email2', 'call_skype', 'send_birthdaydate');
        $duplexfields = array('mobile_phone2', 'email2');
        //Если запрос вернул пустой результат, дорисую одну строку
        if ($result->num_rows == 0) {
            $class = fmod($count, 2) != 1 ? ("impair") : ("pair");
            $table .= "<tr id = 0 class='" . $class . "'>\r\n";
            $num_col = 0;
            for ($i = 0; $i <= $result->field_count; $i++) {
                if ($fields[$i]->name != 'rowid') {
                    if ($result->field_count != $i) {
                        if(in_array($fields[$i]->name, $ignorfields) || in_array($fields[$i]->name, $duplexfields)){}
                        else {
                            $num_col++;
                            $table .= '<td id="0" >&nbsp;</td>';
                        }
                    } elseif($showtitle) {
                        $table .= '<td id="0" style="width: 20px"></td>';
                        $num_col++;
                    }
                }
            }
            $table .= '<td id="0" >&nbsp;</td>';
        }
        while($row = $result->fetch_assoc()) {
//            var_dump($row);
//            die();
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
                if($fields[$num_col]->type == 10) {//Якщо тип поля - дата - перетворюю на правильний формат
                    if(!empty($value)) {
                        $date = new DateTime($value);
                        $value = $date->format('d.m.Y');
                    }
                }

                $col_name = "'".$fields[$num_col]->name."'";

                if(!in_array($cell, $ignorfields) && !in_array($cell, $duplexfields)) {
                    if( count($readonly)==0)//Формирую форму для редактирования
//                        $edit_form.=$this->fBuildEditForm($title[$num_col-1], $fields[$num_col], $theme, $tablename);
//                    var_dump($title[$num_col-1]['title'].' '.$cell.' '.!isset($title[$num_col-1]['hidden']).'</br>');
                    if(!isset($title[$num_col-1]['hidden'])) {
//                        echo '<pre>';
//                        var_dump($title[$num_col-1]['title']);
//                        echo '</pre>';
                        if(count($readonly)==0)
                            $width = ($title[$num_col-1]['width'])!=''?($title[$num_col-1]['width'].'px'):('auto');
                        else
                            $width = ($title[$num_col-1]['width'])!=''?($title[$num_col-1]['width']+(($num_col-1)*1.5).'px'):('auto');

                        if ($fields[$num_col]->type == 16) {
                            if(count($readonly)==0) {
                                if ($value == '1') {
                                    $table .= '<td class = "switch" id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width: ' . $width . '" ><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_on.png" onclick="change_switch(' . $row['rowid'] . ', ' . $tablename . ', ' . $col_name . ');" > </td>';
                                } else {
                                    $table .= '<td class = "switch" id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width: ' . $width . '" ><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_off.png" onclick="change_switch(' . $row['rowid'] . ', ' . $tablename . ', ' . $col_name . ');"> </td>';
                                }
                            }else{
                                if(in_array($row['rowid'], $readonly)){
                                    $table .= '<td class = "switch" id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width: ' . $width . '" ><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_on.png"> </td>';
                                }else{
                                    $table .= '<td class = "switch" id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width: ' . $width . '" ><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_off.png"> </td>';
                                }
                            }
                        } elseif (!empty($title[$num_col - 1]['action'])) {
                            $link = "'" . $title[$num_col - 1]["action"] . '&' . $title[$num_col - 1]["param"] . '=' . $row['rowid'] . "'";
                            $table .= '<td style="width: ' . $width . '" id="' . $row['rowid'] . $fields[$num_col]->name . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . $title[$num_col - 1]["icon_src"] . '" onclick="goto_link(' . $link . ');" > </td>';
//                        echo'<pre>';
//                        var_dump($title[$num_col-1]["action"]);
//                        echo'</pre>';
                        } else {
                            if (substr($fields[$num_col]->name, 0, 2) != 's_') {
//                                if(!empty($value)) {
                                    $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width: ' . $width . '" >';
                                    if($fields[$num_col]->name == 'work_phone' || $fields[$num_col]->name == 'fax' || $fields[$num_col]->name == 'mobile_phone1'||
                                        $fields[$num_col]->name == 'email1' || $fields[$num_col]->name =='skype' || $fields[$num_col]->name == 'birthdaydate') {

                                        $call_pref='';
                                        if($fields[$num_col]->name == 'work_phone' || $fields[$num_col]->name == 'fax'|| $fields[$num_col]->name == 'mobile_phone1' || $fields[$num_col]->name == 'skype')
                                            $call_pref = 'call';
                                        elseif(substr($fields[$num_col]->name, 0, 5) == 'email' || $fields[$num_col]->name == 'birthdaydate')
                                            $call_pref = 'send';
//                                        if($fields[$num_col]->name == 'birthdaydate'){
//                                            var_dump($value);
//                                            die();
//                                        }
                                        $table .= '<table class="contactlist_contact"><tr><td>'.(trim($langs->trans($value))) . '</td>';
                                        if(!empty($value)) {
                                            $ID = "'#img" . $row['rowid'] . $fields[$num_col]->name . "'";
                                            if ($row[$call_pref . "_" . $fields[$num_col]->name] == 0)
                                                $table .= '<td><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/uncheck.png" onclick = "change_switch_callfield($(' . $ID . '));">';
                                            else
                                                $table .= '<td><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/check.png" onclick = "change_switch_callfield($(' . $ID . '));">';
                                            $table .= '</td></tr>';
                                            if (($fields[$num_col]->name == 'mobile_phone1' || $fields[$num_col]->name == 'email1') && !empty($row[$fields[$num_col + 2]->name])) {
                                                $ID = "'#img" . $row['rowid'] . $fields[$num_col + 2]->name . "'";
                                                $table .= '<tr><td>' . $row[$fields[$num_col + 2]->name] . '</td>';
                                                if ($row[$call_pref . "_" . $fields[$num_col + 2]->name] == 0)
                                                    $table .= '<td><img id="img' . $row['rowid'] . $fields[$num_col + 2]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/uncheck.png" onclick = "change_switch_callfield($(' . $ID . '));">';
                                                else
                                                    $table .= '<td><img id="img' . $row['rowid'] . $fields[$num_col + 2]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/check.png" onclick = "change_switch_callfield($(' . $ID . '));">';
//                                            echo '<pre>';
//                                            var_dump($cell, $row[$fields[$num_col+2]->name]) . '</br>';
//                                            echo '</pre>';
                                                $table .= '</td>';
                                            }
                                        }
                                        $table .= '</tr>';
                                        $table .= '</table>';
                                    }else
                                        $table .= (trim($langs->trans($value))) . ' </td>';
//                                }else
//                                    $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width: ' . $width . '" > </td>';
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
                                $table .= '<td  id="' . $row['rowid'] . $fields[$num_col]->name . '" style="width: ' . $width . '" >' . $selectlist . '</td>';
//                            $table .= '<td class = "combobox" id="' . $row['rowid'] . $fields[$num_col]->name . '">' . $value . '</td>';
                            }
                        }
                    }
                }
                $num_col++;
            }
//            if(!$create_edit_form && count($readonly)==0) {
//                $create_edit_form = true;
//                $save_item ="save_item(".$tablename.",'".$hiddenfield;
////                var_dump();
////                die();
//                $edit_form .='    </table>
//                               </form>
//                            <a class="close" title="Закрыть" href="#close"></a>
//                            </br>';
//                if($additionparam) {
//                    $edit_form .= "<script>
//                                var tablename = " . $tablename . ";
//                                var fieldname = '" . $hiddenfield . "';
//                                var sendtable = '" . $sendtable . "';
//                            </script>";
//
//                }else{
//                    $edit_form .= "<script>
//                                var tablename = " . $tablename . ";
//                                var fieldname = '';
//                                var sendtable = '';
//                            </script>";
//                }
//                $edit_form .= '<button onclick="save_item(tablename, fieldname, sendtable)">Сохранить</button>
//                            <button onclick="close_form();">Закрыть</button>
//                            </div>';
//            }
//
//            var_dump(count($readonly)==0);
//            die();
//            if(count($readonly)==0 && $showtitle) {
            $click_link = "/dolibarr/htdocs/societe/addcontact.php?action=edit&mainmenu=companies&rowid=".$row['rowid'];
                $table .= '<td style="width: 20px" align="left">
                <script> var click_event = "'.$click_link.'";</script>
                <img  id="img_'. $row['rowid'].'" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/edit.png" title="'.$langs->trans('Edit').'" style="vertical-align: middle" onclick="location.href=click_event"">


                       </td>';
//            }
            $table .= '</tr>';
        }
        $table .= '</tbody>'."\r\n";
        $table .= '</table>'."\r\n";
//        if(count($readonly)==0)
//            $table .= '</form>'."\r\n";


        return $table;
    }
    function updateContact(){
//        echo '<pre>';
//        var_dump($this);
//        echo '</pre>';
//        die();
        global $db, $user;
        $date = new DateTime($this->birthdaydate);
        $sql='update `llx_societe_contact` set
        `subdivision`= "'.$db->escape(trim($this->subdivision)).'",
        `town_id` = '.(empty($this->town_id)?0:$this->town_id).',
        `post_id` = '.(empty($this->post)?0:$this->post).',
        `respon_id`= '.(empty($this->SphereOfResponsibility)?null:$this->SphereOfResponsibility).',
        `lastname` = "'.$db->escape(trim($this->lastname)).'",
        `firstname` = "'.$db->escape(trim($this->firstname)).'",
        `work_phone`= "'.trim($this->work_phone).'",
        `call_work_phone` = '.(empty($this->call_work_phone)?0:$this->call_work_phone).',
        `fax` = "'.trim($this->fax).'",
        `call_fax` = '.(empty($this->call_fax)?0:$this->call_fax).',
        `mobile_phone1` = "'.trim($this->mobile_phone1).'",
        `call_mobile_phone1` = '.(empty($this->call_mobile_phone1)?0:$this->call_mobile_phone1).',
        `mobile_phone2` = "'.trim($this->mobile_phone2).'",
        `call_mobile_phone2` = '.(empty($this->call_mobile_phone2)?0:$this->call_mobile_phone2).',
        `email1` = "'.trim($this->email1).'",
        `send_email1` = '.(empty($this->send_email1)?0:$this->send_email1).',
        `email2` = "'.trim($this->email2).'",
        `send_email2` = '.(empty($this->send_email2)?0:$this->send_email2).',
        `skype` = "'.trim($this->skype).'",
        `call_skype` = '.(empty($this->call_skype)?0:$this->call_skype).',
        `birthdaydate` = "'.$date->format('Y-m-d').'",
        `send_birthdaydate` = '.(empty($this->send_birthdaydate)?0:$this->send_birthdaydate).',
        `socid` = '.$this->socid.', id_usr = '.$user->id.' where rowid='.$this->rowid;
        $sql = str_replace('"', "'", $sql);
//        die($sql);
        $res = $db->query($sql);
        if(!$res){
            var_dump($sql);
            dol_print_error($db);
        }
    }
    function fetch($rowid){
        global $db, $user;
        $sql = 'select `llx_societe_contact`.rowid, subdivision,  concat(trim(nametown), " ", trim(regions.name), " р-н. ", trim(states.name), " обл.") as location,
            `llx_societe_contact`.`town_id`, `llx_societe_contact`.`post_id` as post,`llx_societe_contact`.`respon_id` as SphereOfResponsibility,
            lastname,firstname,work_phone,call_work_phone,fax,call_fax,mobile_phone1,call_mobile_phone1,mobile_phone2,
            call_mobile_phone2,email1,send_email1,email2,send_email2,skype,call_skype,
            birthdaydate,send_birthdaydate, `llx_societe_contact`.`socid`
            from `llx_societe_contact`
            left join `llx_c_ziptown` on `llx_c_ziptown`.rowid = `llx_societe_contact`.`town_id`
                    left join states on states.rowid = llx_c_ziptown.fk_state
                    left join regions on regions.rowid =  llx_c_ziptown.`fk_region`
            where `llx_societe_contact`.`rowid`='.$rowid;

        $res = $db->query($sql);
        if(!$res){
            var_dump($sql);
            dol_print_error($db);
        }
        $obj = $db->fetch_object($res);
        $this->rowid                    = $rowid;
        $this->subdivision              = $obj->subdivision;//назва структурного підрозділу
        $this->town_id                  = $obj->town_id;//ІД населеного пункту
        $this->location                 = $obj->location;//назва населеного пункту
        $this->post                     = $obj->post;//посада
        $this->SphereOfResponsibility   = $obj->SphereOfResponsibility;//сфера відповідальності
        $this->lastname                 = $obj->lastname;//прізвище
        $this->firstname                = $obj->firstname;//ім'я побатькові
        $this->work_phone               = $obj->work_phone;//робочий телефон
        $this->call_work_phone          = $obj->call_work_phone;//дозвіл дзвонити
        $this->fax                      = $obj->fax;//факс
        $this->call_fax                 = $obj->call_fax;//дозвіл відправляти повідомлення
        $this->mobile_phone1            = $obj->mobile_phone1;//мобільний телефон
        $this->call_mobile_phone1       = $obj->call_mobile_phone1;//дозвіл дзвонити на мобільний телефон
        $this->mobile_phone2            = $obj->mobile_phone2;//мобільний телефон
        $this->call_mobile_phone2       = $obj->call_mobile_phone2;//дозвіл дзвонити на мобільний телефон
        $this->email1                   = $obj->email1;//електронна пошта
        $this->send_email1              = $obj->send_email1;//дозвіл відправляти повідомлення
        $this->email2                   = $obj->email2;//електронна пошта
        $this->send_email2              = $obj->send_email2;//дозвіл відправляти повідомлення
        $this->skype                    = $obj->skype;//логін скайпу
        $this->call_skype               = $obj->call_skype;//дозвіл дзвонити на скайп
        if(!empty($obj->birthdaydate)) {
            $date = new DateTime($obj->birthdaydate);
            $this->birthdaydate = $date->format('d.m.Y');//дата народження
        }
        $this->send_birthdaydate        = $obj->send_birthdaydate;//дозвіл поздоровляти
        $this->socid                    = $obj->socid;

    }
    function fBuildEditForm($title, $field, $theme, $tablename){
        $edit_form ="<tr>
                        <td class='param'>".$title['title']."</td>";


        if(isset($title['hidden'])){
            $sql = "select rowid, name from `".$title['sourcetable']."` where active = 1 and calc = 0";
            $result = $this->mysqli->query($sql);

            $edit_form.="<td id='".$title['detailfield']."' style='height: ".($result->num_rows>5?100:$result->num_rows*24)."px;' class = 'param'>";
            $param_t = "<table>";
            while($row = $result->fetch_assoc()){
//                for($i=0;$i<100;$i++) { //Нужно сделать прокрутку в случае с большим количеством параметров
                $param_t .= "<tr>
                    <td class='param'>" . $row['name'] . "</td>
                    <td class='param'>" . '<input id = ' . $row['rowid'] . ' class="param" maxlength="11" name="label" type="text" value="">' . "</td>
                </tr>";
//                }
            }
            $param_t .= "</table>";
            $edit_form.=$param_t;
        }else {
            $edit_form.="<td>";
            if ($field->type != 16) {
                if (substr($field->name, 0, 2) != 's_') {//Если поле из основной таблицы
                    if ($field->length <= 50)
                        $edit_form .= '<input id = edit_' . $field->name . ' class="edit_text" maxlength="45" name="label" type="text" value="">';
                    else
                        $edit_form .= '<textarea id = edit_' . $field->name . ' class="edit_text" name="description"></textarea>';
                } else {//Если поле из подключенной таблицы
//                    echo '<pre>';
//                    var_dump($title['selrow']);
//                    echo '</pre>';
//                    die();
                    if (substr($field->name, 0, 6) == 's_llx_') {
                        $stpos = 7;
                    } else
                        $stpos = 3;
                    $s_table = substr($field->name, 2, strrpos($field->name, '_', $stpos) - 2);
                    $s_fieldname = substr($field->name, strrpos($field->name, '_', $stpos) + 1);
                    $edit_form .= "\r\n";
                    if (isset($title['detailfield']))
                        $edit_form .= '<input id="detail_' . $s_table . '_' . $s_fieldname . '" type="hidden" value="' . $title['detailfield'] . '">' . "\r\n";
                    if (!$this->selectlist['edit_' . $s_table . '_' . $s_fieldname]) {
                        $this->selectlist['edit_' . $s_table . '_' . $s_fieldname] = '<select class = "combobox" id="edit_' . $s_table . '_' . $s_fieldname . '" name="' . $s_table . '" size="1">' . "\r\n";
                        $sql = "select rowid, " . $s_fieldname . " from " . $s_table . " where active = 1 order by " . $s_fieldname;
//                        die($sql);
                        $result = $this->mysqli->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $this->selectlist['edit_' . $s_table . '_' . $s_fieldname] .= '<option id="option' . $row['rowid'] . '" class="edit_' . $s_table . '_' . $s_fieldname . '" value="' . $row['rowid'] . '" '.(($title['selrow'] == $row['rowid'])?'selected = "selected"':'').'>' . $row[$s_fieldname] . '</option>' . "\r\n";
                        }
                        $this->selectlist['edit_' . $s_table . '_' . $s_fieldname] .= '</select>';
                        $edit_form .= $this->selectlist['edit_' . $s_table . '_' . $s_fieldname];
                    }
                }
            } else {
                $field_name = "'" . $field->name . "'";
                $edit_form .= '<img id="edit_' . $field->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_on.png" onclick="change_switch(0, ' . $tablename . ', ' . $field_name . ');">';
            }
        }
        $edit_form.="</td>
                    </tr>";
        return $edit_form;
    }
} 