<?php
/**
 * Created by PhpStorm.
 * User: tavis
 * Date: 30.09.2015
 * Time: 17:36
 */

class dbBuilder{
    
    public $mysqli;
    public $selectlist=array();
    public function __construct()
    {
        include 'db.php';
        $db = new dbMysqli();
        $this->mysqli = $db->mysqli;
    }
    function fBuildEditForm($title, $field, $theme, $tablename){
        $edit_form ="<tr>
                        <td>".$title['title']."</td>";
        $edit_form.="<td>";
//        echo '<pre>';
//        var_dump($field);
//        echo '</pre>';
//        die();
        if($field->type != 16){
            if(substr($field->name, 0,2) != 's_') {//Если поле из основной таблицы
                if ($field->length <= 50)
                    $edit_form .= '<input id = edit_' . $field->name . ' class="edit_text" maxlength="45" name="label" type="text" value="">';
                else
                    $edit_form .= '<textarea id = edit_' . $field->name . ' class="edit_text" name="description"></textarea>';
            }else{//Если поле из подключенной таблицы
                if(substr($field->name, 0,6)=='s_llx_'){
                    $stpos=7;
                }else
                    $stpos=3;
                $s_table = substr($field->name, 2, strpos($field->name, '_', $stpos)-2);
                $s_fieldname = substr($field->name, strpos($field->name, '_', $stpos)+1);
                $edit_form .= "\r\n";
                if(isset($title['detailfield']))
                    $edit_form .= '<input id="detail_'.$s_table.'_'.$s_fieldname.'" type="hidden" value="'.$title['detailfield'].'">'."\r\n";
                if(!$this->selectlist['edit_'.$s_table.'_'.$s_fieldname]) {
                    $this->selectlist['edit_'.$s_table.'_'.$s_fieldname] = '<select class = "combobox" id="edit_' . $s_table . '_' . $s_fieldname . '" name="' . $s_table . '" size="1">' . "\r\n";
                    $sql = "select rowid, " . $s_fieldname . " from " . $s_table . " where active = 1 order by " . $s_fieldname;

                    $result = $this->mysqli->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $this->selectlist['edit_'.$s_table.'_'.$s_fieldname] .= '<option id="option' . $row['rowid'] . '" class="edit_' . $s_table . '_' . $s_fieldname . '" value="' . $row['rowid'] . '">' . $row[$s_fieldname] . '</option>' . "\r\n";
                    }
                    $this->selectlist['edit_'.$s_table.'_'.$s_fieldname] .= '</select>';
                    $edit_form .= $this->selectlist['edit_'.$s_table.'_'.$s_fieldname];
                }
            }
        }else{
            $field_name = "'".$field->name."'";
            $edit_form.='<img id="edit_' .$field->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_on.png" onclick="change_switch(0, ' . $tablename . ', ' . $field_name . ');">';
        }
        $edit_form.="</td>
                    </tr>";
        return $edit_form;
    }
    public function fShowTable($title = array(), $sql, $tablename, $theme){
        global $user, $conf, $langs, $db;
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
        $result = $this->mysqli->query($sql);

        $fields = $result->fetch_fields();
        $table ='<table width="100%" id="reference" class="noborder">'."\r\n";
        $table .= '<tbody>'."\r\n";
        $table .= '<tr id="reference_title" class="liste_titre">'."\r\n";
        $count = 0;

        foreach($title as $column){
            $table .= '<th ';
            $table .= $column['width']<>''?('width="'.$column['width'].'"'):(' ');//ширина
            $table .= $column['align']<>''?('align="'.$column['align'].'"'):(' ');//выравнивание
            $table .= $column['class']<>''?('class="'.$column['class'].'"'):(' ');//класс
            $table .= '>'.$column['title'].'</th>';
        }

//        echo '<pre>';
//        var_dump($title);
//        echo '</pre>';
//        die();
        $table .= '<td class="nocellnopadd boxclose nowrap">
        <img class="boxhandle hideonsmartphone" border="0" style="cursor:move;" title="" alt="" src="/dolibarr/htdocs/theme/eldy/img/grip_title.png">'."\r\n";
        $table .= '<input id="boxlabelentry18" type="hidden" value="">
        </td>'."\r\n";
        $table .= '</tr>'."\r\n";


        $count = 0;
        $create_edit_form = false;
        $edit_form = "<a href='#x' class='overlay' id='editor'></a>
                     <div class='popup'>
                     <form>
                     <input type='hidden' id='user_id' name='user_id' value=".$user->id.">
                     <input type='hidden' id='edit_rowid' name='rowid' value='0'>
                     <table id='edit_table'>
                     <tbody>";
        //Если запрос вернул пустой результат, дорисую одну строку
        if($result->num_rows == 0){
            $class = fmod($count,2)!=1?("impair"):("pair");
            $table .= "<tr id = 0 class='".$class."'>\r\n";
            $num_col = 0;
            for($i = 0; $i<=$result->field_count;$i++){
                if($fields[$i]->name != 'rowid') {
                    if ($result->field_count != $i) {
                        $table .= '<td id="0"></td>';
                        $edit_form .= $this->fBuildEditForm($title[$num_col], $fields[$i], $theme, $tablename);
                    } else
                        $table .= '<td id="0" style="width: 70px"></td>';
                    $num_col++;
                }
            }
            $edit_form .="    </table>
                               </form>
                            <a class='close' title='Закрыть' href='#close'></a>
                            </br>
                            <button onclick=save_item(".$tablename.");>Сохранить</button>
                            <button onclick='close_form();'>Закрыть</button>
                            </div>
            ";
        }

        while($row = $result->fetch_assoc()) {
            $count++;
            $class = fmod($count,2)==1?("impair"):("pair");
            $table .= "<tr id = tr".$row['rowid']." class='".$class."'>\r\n";
//            $table .= "<tr id = $count class=".fmod($count,2)==1?('impair'):('pair').">\r\n";
            $id = $row['rowid'];
//            $table .= '<td >'.$class.'</td>';
//            echo '<pre>';
//            var_dump($fields);
//            echo '</pre>';
//            die();
            $num_col = 0;
            foreach($row as $cell=>$value){

                $col_name = "'".$fields[$num_col]->name."'";
                if($cell != 'rowid') {
                    if(!$create_edit_form)//Формирую форму для редактирования
                        $edit_form.=$this->fBuildEditForm($title[$num_col-1], $fields[$num_col], $theme, $tablename);
                    if($fields[$num_col]->type == 16){
                        if($value == '1') {
                            $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '"><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_on.png" onclick="change_switch(' . $row['rowid'] . ', ' . $tablename . ', ' . $col_name . ');"> </td>';
                        }else{
                            $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '"><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_off.png" onclick="change_switch(' . $row['rowid'] . ', ' . $tablename . ', ' . $col_name . ');"> </td>';
                        }
                    }
                    else {
                        if(substr($fields[$num_col]->name,0,2)!='s_') {
                            $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '">' . $value . '</td>';
                        }else{

                            if(substr($fields[$num_col]->name, 0,6)=='s_llx_'){
                                $stpos=7;
                            }else
                                $stpos=3;
                            $s_table = substr($fields[$num_col]->name, 2, strpos($fields[$num_col]->name, '_', $stpos)-2);
                            $s_fieldname = substr($fields[$num_col]->name, strpos($fields[$num_col]->name, '_', $stpos)+1);

                            $selectlist = substr($this->selectlist['edit_'.$s_table.'_'.$s_fieldname], 0, strpos($this->selectlist['edit_'.$s_table.'_'.$s_fieldname], $value)-1).' selected = "selected" '.substr($this->selectlist['edit_'.$s_table.'_'.$s_fieldname], strpos($this->selectlist['edit_'.$s_table.'_'.$s_fieldname], $value)-1);
                            $selectlist = str_replace('class="edit_'.substr($fields[$num_col]->name, 2).'"', '',$selectlist);

                            if(isset($title[$num_col-1]["detailfield"])){
                                $selectlist = str_replace('id="edit_'.substr($fields[$num_col]->name, 2).'"', 'id="select'.$row['rowid'].$title[$num_col-1]["detailfield"].'"',$selectlist);
                                $detailfield = "'".$title[$num_col-1]["detailfield"]."'";
                                $selectlist = str_replace('<select', '<select onChange="change_select('. $row['rowid'] .', ' . $tablename . ', ' . $detailfield . ');"', $selectlist);
                            }
//                            echo '<pre>';
//                            var_dump(htmlspecialchars($selectlist));
//                            echo '</pre>';
//                            die();
                            $table .= '<td  id="' . $row['rowid'] . $fields[$num_col]->name . '">' . $selectlist . '</td>';
//                            $table .= '<td class = "combobox" id="' . $row['rowid'] . $fields[$num_col]->name . '">' . $value . '</td>';
                        }
                    }



                }
                $num_col++;
            }
            if(!$create_edit_form) {
                $create_edit_form = true;
                $edit_form .="    </table>
                               </form>
                            <a class='close' title='Закрыть' href='#close'></a>
                            </br>
                            <button onclick=save_item(".$tablename.");>Сохранить</button>
                            <button onclick='close_form();'>Закрыть</button>
                            </div>
            ";
            }
//
            $table .= '<td style="width: 20px" align="left">

                <img  src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/edit.png" title="Редактировать" style="vertical-align: middle" onclick="edit_item('.$row['rowid'].');">


                       </td>';
            $table .= '</tr>';
        }
        $table .= '</tbody>'."\r\n";
        $table .= '</table>'."\r\n";
        $table .= '</form>'."\r\n";

        $table .= $edit_form;
        return $table;
    }
}



