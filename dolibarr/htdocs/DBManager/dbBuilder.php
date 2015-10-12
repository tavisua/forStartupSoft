<?php
/**
 * Created by PhpStorm.
 * User: tavis
 * Date: 30.09.2015
 * Time: 17:36
 */

class dbBuilder{
    
    public $mysqli;
    public function __construct()
    {
        include 'db.php';
        $db = new dbMysqli();
        $this->mysqli = $db->mysqli;
    }
    function fBuildEditForm($title, $field, $theme, $tablename){
        $edit_form ="<tr>
                        <td>".$title."</td>";
        $edit_form.="<td>";
        if($field->type != 16){
            if($field->length<=50)
                $edit_form.='<input id = edit_'.$field->name.' class="edit_text" maxlength="45" name="label" type="text" value="">';
            else
                $edit_form.='<textarea id = edit_'.$field->name.' class="edit_text" name="description"></textarea>';
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
//        var_dump($sql);
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
                        $edit_form .= $this->fBuildEditForm($title[$num_col]['title'], $fields[$i], $theme, $tablename);
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
            $table .= "<tr id = ".$row['rowid']." class='".$class."'>\r\n";
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
                        $edit_form.=$this->fBuildEditForm($title[$num_col-1]['title'], $fields[$num_col], $theme, $tablename);
                    if($fields[$num_col]->type == 16){
                        if($value == '1') {
                            $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '"><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_on.png" onclick="change_switch(' . $row['rowid'] . ', ' . $tablename . ', ' . $col_name . ');"> </td>';
                        }else{
                            $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '"><img id="img' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_off.png" onclick="change_switch(' . $row['rowid'] . ', ' . $tablename . ', ' . $col_name . ');"> </td>';
                        }
                    }
                    else
                        $table .= '<td id="' . $row['rowid'] . $fields[$num_col]->name . '">' . $value . '</td>';



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



