<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 27.11.2015
 * Time: 4:17
 */

//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
//require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
//llxHeader();

$taborder = array(1,2,3,4,5,6,7);
$tabname = array();
$tabname[1] = array('title'=>'Повнота знань про контрагентів</br>та їх посадових осіб');
$tabname[2] = array('title'=>'Організаційна робота</br>&nbsp;');
$tabname[3] = array('title'=>'Сумма земель що</br>обробляють відомі нам аграрії');
$tabname[4] = array('title'=>'Деталізація перемовин</br>&nbsp;');
$tabname[5] = array('title'=>'Частота перемовин</br>&nbsp;');
$tabname[6] = array('title'=>'По потребах і угодах</br>&nbsp;');
$tabname[7] = array('title'=>'Деталізація</br>результату і перевірок');
llxLoadingForm();
TitlePagePerformance();
TitleResponsibility();
exit();

function TitleResponsibility(){
    global $taborder,$db;
    if((!isset($_REQUEST['active_page'])||empty($_REQUEST['active_page'])))
        $pageindex = $taborder[0];
    else
        $pageindex = $_REQUEST['active_page'];
    switch($pageindex){
        case 1:{
//            echo '<pre>';
//            var_dump($_REQUEST);
//            echo '</pre>';
            require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
            $form = new Form($db);
            $object = new User($db);
            $responsibility = $form->select_control(!empty($_REQUEST['respon_id'])?$_REQUEST['respon_id']:'', 'respon_id', 0, 'responsibility', 'name', $object, false, '185px');
            if(empty($_REQUEST['respon_id'])) {
                $lineactive = EmptySelect('lineactive');
                $state = EmptySelect('states');
            }else
                $lineactive = $form->select_control('', 'respon_id', 0, 'responsibility', 'name', $object, false, '185px');
            include DOL_DOCUMENT_ROOT.'/theme/eldy/responsibility/gen_dir/performance/customer_knowdata.html';
        }
    }
}
function EmptySelect($name){
    $out='<select class="combobox" id="'.$name.'" name="'.$name.'" disabled="true" style="width: 185px"></select>';
    return $out;
}
function TitlePagePerformance(){
    global $taborder,$tabname;
    $showing = false;
    print '<div id="TitleTabs" class="tabs" data-type="horizontal" data-role="controlgroup">';
        foreach($taborder as $value) {
            if((!isset($_REQUEST['active_page'])||empty($_REQUEST['active_page']))&&!$showing) {
                $active = 'tabactive';
                $showing = true;
            }elseif($_REQUEST['active_page'] == $value) {
                $active = 'tabactive';
                $showing = true;
            }else
                $active = '';
            print '<div class="inline-block tabsElem tabsElemActive">';
            print '<a id="user" class="'.$active.' tab inline-block" href="?idmenu=5216&mainmenu=home&leftmenu=&active_page='.$value.'" data-role="button">'.$tabname[$value]['title'].'</a>';
            print '</div>';
        }
    print '</div>';

    print '</div>';
}


