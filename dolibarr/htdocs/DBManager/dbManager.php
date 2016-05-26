<?php
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();

    if(isset($_REQUEST['edit'])){
        edit_item();
    }elseif(isset($_REQUEST['save'])){
        save_item();
    }elseif(isset($_REQUEST['set_permission'])){
        set_permission();
    }elseif(isset($_REQUEST['loadparam'])){
        load_param();
    }
    function load_param(){
//        echo '<pre>';
//        var_dump($_REQUEST);
//        echo '</pre>';
//        die();
        include 'db.php';
        $db = new dbMysqli();
        $sql = 'select `'.$_REQUEST['loadfield'].'`, `value` from `'.$_REQUEST['tablename'].'` where `'.$_REQUEST['col_name'].'`='.$_REQUEST['rowid'];
        $res = $db->mysqli->query($sql);

//        die($sql);
        $param=''; $values='';
        while($row = $res->fetch_assoc()){
            if(empty($param)) {
                $param = $row[$_REQUEST['loadfield']];
                $values = $row['value'];
            }else {
                $param .= ',' . $row[$_REQUEST['loadfield']];
                $values .= ','.$row['value'];
            }
        }
        echo 'param='.$param.'; values='.$values;
    }
    function set_permission(){
        include 'db.php';
        if(isset($_REQUEST['group_id'])){
            $fieldname = 'fk_usergroup';
            $ID = $_REQUEST['group_id'];
        }else{
            $fieldname = 'fk_user';
            $ID = $_REQUEST['user_id'];
        }

        $db = new dbMysqli();
        $sql = 'select count(*) iCount from `'.$_REQUEST['table'].'` where 1 and `'.$fieldname.'`='.$ID.' and fk_id='.$_REQUEST['perm_index'];
        $res = $db->mysqli->query($sql);
        $check = $_REQUEST['check'] == 'true'?1:0;
        if($res->fetch_assoc()['iCount'] == 0){
            $sql = 'insert into `'.$_REQUEST['table'].'` (`'.$fieldname.'`, fk_id, active, id_usr, dtChange) values ('.$ID.', '.$_REQUEST['perm_index'].', '.$check.', '.$_REQUEST['id_usr'].', Now())';
        }else{
            $sql = 'update `'.$_REQUEST['table'].'` set active = '.$check.', id_usr='.$_REQUEST['id_usr'].', dtChange=Now() where  `'.$fieldname.'`='.$ID.' and fk_id= '.$_REQUEST['perm_index'];
        }
        $res = $db->mysqli->query($sql);
        if($res)
            echo 'success';
        else
            echo $sql;
    }

    function edit_item(){
        global $user;
        include 'db.php';
        $db = new dbMysqli();
        if($_REQUEST['value']=='true' || $_REQUEST['value']=='false'){
            $value = ($_REQUEST['value']=='true')?'1':'0';
        }else
            $value = $_REQUEST['value'];
        $sql = 'update `'.$_REQUEST['tablename'].'` set `'.$_REQUEST['col_name'].'`='.$value.', `id_usr`='.$_REQUEST['id_usr'].' where `rowid`='.$_REQUEST['rowid'];
        $res = $db->mysqli->query($sql);
        if($res)
            echo 'success';
        else
            echo $sql;
    }
    function save_item(){

        $fields = str_replace(',',"`,`", $_REQUEST['columns']);
        $fields = str_replace("'","`", $fields);
        $fields .= ",`id_usr`";

        $values = str_replace('$$','&#', $_REQUEST['values']);
        //        var_dump(htmlspecialchars($values).'</br>');
        $values = str_replace('@@','&', $values);

        $values = str_replace(',',"','", $values);


//        $values = str_replace('__',',', $_REQUEST['values']);

        $values .= ','.$_REQUEST['id_usr'];

        include 'db.php';
        $db = new dbMysqli();
        $values = $db->mysqli->real_escape_string($values);
        return $values;
        $sql = "select * from `". $_REQUEST['tablename'] ."` limit 1";
        $res = $db->mysqli->query($sql);
        $fieldslist = $res->fetch_fields();

        if(!isset($_REQUEST['rowid'])) {
            $sql = "insert `" . $_REQUEST['tablename'] . "`(" . $fields . ") values(" . $values . ")";
        }else{
            $sql = "update `" . $_REQUEST['tablename'] . "` set ";
//            $values = str_replace("'1'", "1", $values);
            $fields_array = explode(',', $fields);
            $values_array = explode(',', $values);
//            var_dump($values_array);
//            die();
            $num=0;

            foreach($fields_array as $field){
                if($num != 0)
                    $sql .= ", ";
                $datatype = 0;
                for($i=0;$i<count($fieldslist);$i++){
//                    echo(substr(trim($field), 1, strlen(trim($field)) - 2).' '.$fieldslist[$i]->name)."</br>";
                    if($fieldslist[$i]->name == substr(trim($field), 1, strlen(trim($field)) - 2)){
                        $datatype = $fieldslist[$i]->type;
                        break;
                    }
                }
//                echo($field.' '.$datatype)."</br>";

                    if ($datatype == 253 || $datatype == 12 || $datatype == 3) {
                        $sql .= $field . '=' . htmlspecialchars((str_replace('__', ',', trim($values_array[$num]))));
                    } else {
                        $sql .= $field . '=' . htmlspecialchars(substr($values_array[$num], 1, strlen(trim($values_array[$num])) - 2));
                    }

                $num++;
            }
            $sql .= " where rowid=".$_REQUEST['rowid'];
//            var_dump($sql);
//            die();
        }

//        echo '<pre>';
//        var_dump($values_array);
//        var_dump($sql);
//        echo '</pre>';
//        die();
        $res = $db->mysqli->query($sql);
        if($res) {
            if(!isset($_REQUEST['rowid'])){
                $sql='select max(rowid)rowid from '.$_REQUEST['tablename'];
                $res = $db->mysqli->query($sql);
                $rowid = $res->fetch_assoc();
                $_REQUEST['rowid']=$rowid['rowid'];
            }
            //Зберігаю додаткові параметри
            if(isset($_REQUEST["paramtable"])){
//                echo '<pre>';
//                var_dump($_REQUEST);
//                echo '</pre>';
//                die();
                $param_array = explode(',', $_REQUEST["param"]);
                $values_array = explode(',', $_REQUEST["pvalues"]);
                for($i=0;$i<count($param_array);$i++){
                    $sql = 'select rowid from `'.$_REQUEST["paramtable"].'` where `'.$_REQUEST["tablename"].'_id`='.$_REQUEST['rowid'].' and `'.$_REQUEST["paramfield"].'` = '.$param_array[$i].' limit 1';
                    $res = $db->mysqli->query($sql);
                    if($res->num_rows == 0){
                        $sql = 'insert into `'.$_REQUEST["paramtable"].'` (`'.$_REQUEST["tablename"].'_id`,`'.$_REQUEST["paramfield"].'`, `value`, id_usr, dtChange)
                         values('.$_REQUEST['rowid'].', '.$param_array[$i].', '.$values_array[$i].', '.$_REQUEST["id_usr"].', Now())';
                    }else{
                        $sql = 'update `'.$_REQUEST["paramtable"].'` set value='.$values_array[$i].', id_usr='.$_REQUEST["id_usr"].', dtChange=Now()
                            where `'.$_REQUEST["tablename"].'_id`='.$_REQUEST['rowid'].' and `'.$_REQUEST["paramfield"].'` = '.$param_array[$i];
                    }
                    $db->mysqli->query($sql);
                }


            }
            echo $_REQUEST['rowid'];
        }else
            echo $sql;
    }
