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
    public function fShowTable($title = array(), $sql, $tablename, $theme){
        $result = $this->mysqli->query($sql);
        $fields = $result->fetch_fields();
        $table ='<table width="100%" class="noborder">'."\r\n";
        $table .= '<tbody>'."\r\n";
        $table .= '<tr class="liste_titre">'."\r\n";
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
        <img class="boxhandle hideonsmartphone" border="0" style="cursor:move;" title="Переместить поле 18" alt="" src="/dolibarr/htdocs/theme/eldy/img/grip_title.png">'."\r\n";
        $table .= '<img id="imgclose18" class="boxclose" border="0" style="cursor:pointer;" rel="x:y" title="Закрыть" alt="" src="/dolibarr/htdocs/theme/eldy/img/close_title.png">
        <input id="boxlabelentry18" type="hidden" value="Последние 5 изменённых предложений">
        </td>'."\r\n";
        $table .= '</tr>'."\r\n";


        $count = 0;
        while($row = $result->fetch_assoc()) {
            $count++;
            $class = fmod($count,2)==1?("impair"):("pair");
            $table .= "<tr id = $count class='".$class."'>\r\n";
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
                    if($fields[$num_col]->type == 16){
                        if($value == '1') {
                            $table .= '<td><img id="' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_on.png" onclick="change_switch(' . $row['rowid'] . ', ' . $tablename . ', ' . $col_name . ');"> </td>';
                        }else{
                            $table .= '<td><img id="' . $row['rowid'] . $fields[$num_col]->name . '" src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/switch_off.png" onclick="change_switch(' . $row['rowid'] . ', ' . $tablename . ', ' . $col_name . ');"> </td>';
                        }
                    }
                    else
                        $table .= '<td>' . $value . '</td>';



                }
                $num_col++;
            }
            $table .= '<td style="width: 20px" align="left">
                            <button style="width: 35px" title = "Редактировать" onclick="edit_item('.$row['rowid'].', '.$tablename.');">
                                <img src="' . DOL_URL_ROOT . '/theme/' . $theme . '/img/edit.png" alt="Редактировать" style="vertical-align: middle">
                            </button>

                       </td>';
            $table .= '</tr>';
        }
        $table .= '</tbody>'."\r\n";
        $table .= '</table>';
        $table .= "<script>
                    function del_confirm(rowid, tablename) {
                        if (confirm('Удалить пункт меню?'))
                            location.href = 'http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?rowid='+rowid+'&del=1&tablename='+tablename;
                    }
                    function edit_item(rowid, tablename){
                        location.href ='http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?rowid='+rowid+'&edit=1&tablename='+tablename;
                    }
                    function change_switch(rowid, tablename, col_name){
                        var x = document.getElementById(rowid+col_name);
                        var check = false;
                        if(x.src == 'http://".$_SERVER["SERVER_NAME"].DOL_URL_ROOT."/theme/".$theme."/img/switch_on.png'){
                            x.src = 'http://".$_SERVER["SERVER_NAME"].DOL_URL_ROOT."/theme/".$theme."/img/switch_off.png';
                        }else{
                            x.src = 'http://".$_SERVER["SERVER_NAME"].DOL_URL_ROOT."/theme/".$theme."/img/switch_on.png';
                            check = true;
                        }
                        update_data(rowid, tablename, col_name, check);
                    }
                    function update_data(rowid, tablename, col_name, check){
                        $.ajax({
                            url: 'http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?rowid='+rowid+'&edit=1&tablename='+tablename+'&col_name='+col_name+'&value='+check,
                            cache: false,
                            success: function (html) {
                                console.log(html);
                            }
                        });

                    };

                    </script>";
        return $table;
    }
}



