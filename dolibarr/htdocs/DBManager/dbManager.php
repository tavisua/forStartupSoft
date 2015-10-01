<?php

    if(isset($_REQUEST['edit'])){
        edit();
    }

    function edit(){
        include 'db.php';
        $db = new dbMysqli();
        if($_REQUEST['value']=='true' || $_REQUEST['value']=='false'){
            $value = ($_REQUEST['value']=='true')?'1':'0';
        }else
            $value = $_REQUEST['value'];
        $sql = 'update `'.$_REQUEST['tablename'].'` set `'.$_REQUEST['col_name'].'`='.$value.' where `rowid`='.$_REQUEST['rowid'];
        $res = $db->mysqli->query($sql);
        if($res)
            echo 'success';
        else
            echo $sql;
    }
