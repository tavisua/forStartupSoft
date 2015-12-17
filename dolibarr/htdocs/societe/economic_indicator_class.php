<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 15.12.2015
 * Time: 10:49
 */

class EconomicIndicator {
    var $socid;

    public function __construct($socid=0)
    {
        $this->socid = $socid;
    }
    public function fixed_assets(){
        global $langs;
        $YearCount = 0;
        if($YearCount>0) {
            $TitleYears = '<th>2015</th>';
        }else
            $TitleYears = '';
        $fixed_assets='';

        if(empty($fixed_assets)){
            $fixed_assets.='<tbody><tr class="impair"><td id="emptyrow_fixed_assets" colspan="13" style="text-align: center; font-weight: bold; color: red">
                    ДАНІ ВІДСУТНІ
                </td></tr></tbody>';
        }
        include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/economic_inditacors/sale/fixed_assets.html';
    }
    public function materials(){
        global $langs;
        $materials='';

        if(empty($materials)){
            $materials.='<tbody><tr class="impair"><td id="emptyrow_materials" colspan="13" style="text-align: center; font-weight: bold; color: red">
                    ДАНІ ВІДСУТНІ
                </td></tr></tbody>';
        }
        include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/economic_inditacors/sale/materials.html';
    }
    public function lineactive($rowid=0){
        global $db;
        $out = '<select id="lineactive" name="lineactive" class="combobox" size="1">';
        $out .='<option value="0" disabled="disabled" selected="selected">Виберіть напрямок</option>';
        $sql = 'select rowid, LCASE (line)line from llx_c_line_active where active = 1 order by line';
        $res = $db->query($sql);
        while($row = $db->fetch_object($res)){
            $out .='<option value = '.$row->rowid.' '.($rowid == $row->rowid?('selected="selected"'):'').'>'.$row->line.'</option>';
        }
        $out .= '</select>';
        return $out;
    }
    public function kind_assets($lineactive=0, $rowid=0){
        global $db;
        $out = '<select id="kind_assets" name="kind_assets" class="combobox" size="1">';
        $out .='<option value="0" disabled="disabled" selected="selected">Вкажіть вид засобів</option>';
        if($lineactive==0){
            $out .= '</select>';
            return $out;
        }
        $sql = 'select rowid, kind_assets from llx_c_kind_assets where fx_line_active='.$lineactive.' and active = 1';
        $res = $db->query($sql);
        while($row = $db->fetch_object($res)){
            $out .='<option value = '.$row->rowid.' '.($rowid == $row->rowid?('selected="selected"'):'').'>'.$row->kind_assets.'</option>';
        }
        $out .= '</select>';
        return $out;
    }
    public function trademark($rowid=0){
        global $db, $langs;
        $out = '<select id="trademark" name="trademark" class="combobox" size="1">';
        $out .='<option value="0" disabled="disabled" selected="selected">'.$langs->trans("Trademark").'</option>';
        $sql = 'select rowid, trademark from llx_c_trademark where active = 1 order by trademark';
        $res = $db->query($sql);
        while($row = $db->fetch_object($res)){
            $out .='<option value = '.$row->rowid.' '.($rowid == $row->rowid?('selected="selected"'):'').'>'.$row->trademark.'</option>';
        }
        $out .= '</select>';
        return $out;
    }
    public function select_month($htmlname='', $id=0){
        global $langs;
        $out = '<select id="'.$htmlname.'" name="'.$htmlname.'" class="combobox" size="1">';
        $out .='<option value="-1" disabled="disabled" selected="selected">'.$langs->trans("SelectMonth").'</option>';
        for($i=1; $i<=12; $i++){
            $out .='<option value = '.$i.' '.($id == $i?('selected="selected"'):'').'>'.$langs->trans("Month".($i<10?'0':'').$i).'</option>';
        }
        $out .= '</select>';
        return $out;
    }

} 