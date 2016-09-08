<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 29.08.2016
 * Time: 5:33
 */
require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
switch($_REQUEST['action']){
    case 'getStates':{
        echo getStates();
    }break;
    case 'getRegions':{
        echo getRegions($_REQUEST['stateID']);
    }break;
}
exit();
function getRegions($stateID){
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();
    global $db, $user;
    $sql = "select distinct case when `fx_category_counterparty` is null then `other_category` else `fx_category_counterparty` end  category_id from `responsibility_param`
          where `fx_responsibility` = ".$user->respon_id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $categoryID = array();
    while($obj = $db->fetch_object($res)){
        if(!in_array($obj->category_id, $categoryID))
            $categoryID[]=$obj->category_id;
    }
    $sql = "select distinct `regions`.rowid, `regions`.`name`
        from `llx_societe`
        left join `llx_societe_address` on `llx_societe_address`.`fk_soc` = `llx_societe`.`rowid`
        inner join `regions` on `regions`.rowid = case when `llx_societe_address`.region_id is null then `llx_societe`.region_id else `llx_societe_address`.region_id end
        where 1 and `llx_societe`.`categoryofcustomer_id` in (".implode(',',$categoryID).") and `llx_societe`.active = 1
        and case when `llx_societe_address`.state_id is null then `llx_societe`.state_id else `llx_societe_address`.state_id end = ".$stateID."
        and case when `llx_societe_address`.region_id is null then `llx_societe`.region_id else `llx_societe_address`.region_id end is not null
        order by `regions`.`name`";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out='';
//    var_dump($_REQUEST['checked']=='false');
//    die();
    while($obj =  $db->fetch_object($res)){
        $out.='<tr id="region'.$obj->rowid.'" class="regions middle_size">
                <td></td>
                <td>'.$obj->name.'</td>
                <td>
                    <div style="width:34px" onclick="checkBoxClick($(this));"><img class="state'.$stateID.'" src="/dolibarr/htdocs/theme/eldy/img/'.($_REQUEST['checked']=='true'?'check.png':'uncheck.png').'"></div>
                </td>
               </tr>';
    }
    return $out;
}
function getStates(){
    global $db, $user;
    $sql = "select distinct `states`.rowid, `states`.`name` from llx_societe
        inner join
          (select distinct case when `fx_category_counterparty` is null then `other_category` else `fx_category_counterparty` end  category_id from `responsibility_param`
          where `fx_responsibility` = ".$user->respon_id.") category on category.category_id = llx_societe.`categoryofcustomer_id`
        inner join `states` on `states`.`rowid` = llx_societe.state_id
        where 1
        and `states`.`active` = 1
        order by `states`.`name`;";

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '<a class="close" style="z-index:10" onclick="closeForm($('."'#LocationFilter'".'));" title="Закрити"></a>
        <table class="WidthScroll LocationFilter" style="background: #ffffff;">
        <thead>
            <tr class="multiple_header_table"><th class="middle_size">
                <th colspan="3">
                    Вкажіть область/район
                </th>
            </tr>
        </thead>
            <tbody style="height:250px;width:auto">';
    while($obj = $db->fetch_object($res)){
        $out.='<tr id="state'.$obj->rowid.'">
            <td>
                <button id="btnState'.$obj->rowid.'" onclick="GetRegions('.$obj->rowid.');">
                    <img src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png">
                </button>
            </td>
            <td class="middle_size">
                '.$obj->name.'
            </td>
            <td>
                <div style="width:34px" onclick="checkBoxClick($(this));"><img src="/dolibarr/htdocs/theme/eldy/img/uncheck.png"></div>
            </td>
        </tr>';
    }
    $out.='</tbody>
        </table>
        <div style="background: #ffffff;"><button onclick="setFilter();">Прийняти</button><button onclick="closeForm($('."'#LocationFilter'".'));">Відмінити</button></div>
        <script>
            function setFilter(){
                var img = $("#LocationFilter").find("img");
                var stateID=[];
                var regionID=[];
                $.each(img,function(i,val){
                    var src = val.src;
                    if(src.substr(src.length-"check.png".length) == "check.png" && src.substr(src.length-"uncheck.png".length) != "uncheck.png"){
                        console.log(val.parentElement.parentElement.parentElement.id);
                        if(val.parentElement.parentElement.parentElement.id.substr(0,"state".length) == "state")
                            stateID.push(val.parentElement.parentElement.parentElement.id.substr("state".length));
                        else if(val.parentElement.parentElement.parentElement.id.substr(0,"region".length) == "region")
                            regionID.push(val.parentElement.parentElement.parentElement.id.substr("region".length));
                    }
                })
            var searchString = location.search.substr(1).split("&");
            var searchParam = {};
            $.each(searchString, function (index, value) {
                searchParam[value.substr(0, strpos(value, "="))] = value.substr(strpos(value, "=") + 1);
                //console.log(value.substr(strpos(value, "=")+1), strpos(value, "="));
            })
            //console.log($("#autorefresh").attr("checked"));

                searchParam["stateID"] = stateID.toString()
                searchParam["regionID"] = regionID.toString()
            searchString = "?";
            $.each(searchParam, function (index, value) {
                console.log(searchString.substr(searchString.length - 1, 1));
                if (searchString.substr(searchString.length - 1, 1) != "?")
                    searchString += "&";
                searchString += index + "=" + value;
            })
            location = location.pathname + searchString;
//            console.log(location.pathname + searchString);

//                console.log($("#LocationFilter").find("img"));
            }
            function checkBoxClick(obj){
                var src = obj.find("img").attr("src");
                var path="";
                var img;
                if(src.substr(src.length-"uncheck.png".length) == "uncheck.png"){
                    path = src.substr(0,src.length-"uncheck.png".length);
                    img = "check.png";
                }else{
                    path = src.substr(0,src.length-"check.png".length);
                    img = "uncheck.png";
                }
                obj.find("img").attr("src", path+img);
                console.log($("."+obj.parent().parent().attr("id")).length > 0);
                if($("."+obj.parent().parent().attr("id")).length > 0){
                    $("."+obj.parent().parent().attr("id")).attr("src",path+img);
                }
            }
        </script>
        <style>
            .regions{
                background-color: #eff0ef;
            }
            .LocationFilter{
                padding-top: 0!important;
            }
        </style>';
    return $out;
}