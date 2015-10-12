<?php

    if(isset($_REQUEST['edit'])){
        edit_item();
    }elseif(isset($_REQUEST['save'])){
        save_item();
    }

    function edit_item(){
        include 'db.php';
        $db = new dbMysqli();
        if($_REQUEST['value']=='true' || $_REQUEST['value']=='false'){
            $value = ($_REQUEST['value']=='true')?'1':'0';
        }else
            $value = $_REQUEST['value'];
        $sql = 'update `'.$_REQUEST['tablename'].'` set `'.$_REQUEST['col_name'].'`='.$value.', `id_usr`='.$_REQUEST['id_usr'].', dtChange=Now() where `rowid`='.$_REQUEST['rowid'];
        $res = $db->mysqli->query($sql);
        if($res)
            echo 'success';
        else
            echo $sql;
    }
    function save_item(){
        include 'db.php';
        $fields = str_replace(',',"`,`", $_REQUEST['columns']);
        $fields = str_replace("'","`", $fields);
        $fields .= ", `id_usr`, `dtChange`";
        $values = str_replace('@@','&', $_REQUEST['values']);
        $values = str_replace(',',"','", $values);
        $values .= ', '.$_REQUEST['id_usr'].', Now()';

        $db = new dbMysqli();
        $sql = "select * from `". $_REQUEST['tablename'] ."` limit 1";
        $res = $db->mysqli->query($sql);
        $fieldslist = $res->fetch_fields();

        if(!isset($_REQUEST['rowid'])) {
            $sql = "insert `" . $_REQUEST['tablename'] . "`(" . $fields . ") values(" . $values . ")";
        }else{
            $sql = "update `" . $_REQUEST['tablename'] . "` set ";
            $fields_array = explode(',', $fields);
            $values_array = explode(',', $values);
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
                if($datatype == 253 || $datatype == 12||$datatype == 3) {
                    $sql .= $field . '=' . trim($values_array[$num++]);
                }else {
                    $sql .= $field . '=' .  substr($values_array[$num], 1, strlen(trim($values_array[$num++])) - 2);
                }
            }
            $sql .= " where rowid=".$_REQUEST['rowid'];
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
            echo $_REQUEST['rowid'];
        }else
            echo $sql;
    }
