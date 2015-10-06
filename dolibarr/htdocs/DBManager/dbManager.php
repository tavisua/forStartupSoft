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
                $sql .= $field.'='.$values_array[$num++];
            }
            $sql .= " where rowid=".$_REQUEST['rowid'];
        }

//        var_dump($db->mysqli->real_escape_string($sql));
//        die();
        $res = $db->mysqli->query($sql);
        if($res)
            echo 'success';
        else
            echo $sql;
    }
