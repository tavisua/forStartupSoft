<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 15.12.2015
 * Time: 10:49
 */

class EconomicIndicator {
    var $rowid;
    var $socid;
    var $line_active;
    var $kindassets;
    var $trademark;
    var $for_what;
    var $count;
    var $year;
    var $container;
    var $time_purchase;
    var $rate;
    var $time_purchase2;
    var $rate2;
    var $PositiveResponse;
    var $NegativeResponse;
    var $contact;
    var $model;
    var $UnMeasurement;
    var $ContainerUnMeasurement;

    public function __construct($socid=0)
    {
        $this->socid = $socid;
    }

    public function fixed_assets(){
        global $langs, $db, $conf;
        $YearCount = 0;
        $sql = 'select distinct DATE_FORMAT(`llx_societe_economic_indicator`.`dtChange`, "%Y") as "Date"
            from `llx_societe_economic_indicator`
            left join `llx_c_line_active` on `llx_c_line_active`.rowid=line_active
            where `llx_societe_economic_indicator`.`socid` = '.$this->socid.'
            and `llx_c_line_active`.fx_type_indicator = 1
            and `llx_societe_economic_indicator`.`active`= 1 order by `Date`';
        $res = $db->query($sql);
        if(!$res){
            var_dump($sql);
            dol_print_error($db);
        }
        $YearCount = $db->num_rows($res);
        $Years = array();
        if($YearCount>0) {
            $TitleYears = '';
            while($row = $db->fetch_object($res)) {
                $TitleYears .= '<th class="small_size" style="width: 43px">'.trim($row->Date).'</th>';
                $Years[] = $row->Date;
            }
        }else
            $TitleYears = '';

        $sql = "select `llx_c_model`.rowid as id_model, `llx_c_line_active`.line, `llx_c_kind_assets`.kind_assets, `llx_c_trademark`.trademark, `llx_c_model`.model, `llx_c_model`.description,
         `llx_societe_economic_indicator`.`year`, '', '',  `llx_societe_economic_indicator`.`PositiveResponse`,  `llx_societe_economic_indicator`.`NegativeResponse`,
         `llx_societe_contact`.`lastname`, `llx_societe_contact`.`firstname`, DATE_FORMAT(`llx_societe_economic_indicator`.`dtChange`, '%Y') as `InsertedDate`,
         round(`llx_societe_economic_indicator`.count, 0) count, `llx_societe_economic_indicator`.`dtChange`
         from `llx_societe_economic_indicator`
        left join `llx_c_line_active` on `llx_c_line_active`.rowid=line_active
        left join `llx_c_kind_assets` on `llx_c_kind_assets`.rowid=kindassets
        left join `llx_c_trademark` on `llx_c_trademark`.rowid=`llx_societe_economic_indicator`.trademark
        left join `llx_c_model` on `llx_c_model`.rowid = `llx_societe_economic_indicator`.model
        left join `llx_societe_contact` on `llx_societe_contact`.rowid =  `llx_societe_economic_indicator`.contact
        where `llx_societe_economic_indicator`.`socid`=".$this->socid."
        and `llx_societe_economic_indicator`.`active`= 1
        and `llx_c_line_active`.fx_type_indicator = 1
        order by line, kind_assets, trademark, `llx_c_model`.rowid, `llx_societe_economic_indicator`.`dtChange` desc";
//        die($sql);

        $restable = $db->query($sql);
        if(!$restable){
            var_dump($sql);
            dol_print_error($db);
        }

        $fixed_assets='';
        if($db->num_rows($restable)>0){
            $fixed_assets.='<tbody class="economic_indicators">';
            $table = array();
            while($row = $db->fetch_array($restable)){
//                var_dump($row);
//                die();
                if(!array_key_exists('id_model'.$row['id_model'], $table)) {
                    $item = array('id_model' => $row['id_model'],
                        'line' => $row['line'],
                        'kind_assets' => $row['kind_assets'],
                        'trademark' => $row['trademark'],
                        'model' => $row['model'],
                        'description' => $row['description'],
                        'year' => $row['year'],
                        'PositiveResponse' => $row['PositiveResponse'],
                        'NegativeResponse' => $row['NegativeResponse'],
                        'contact' => $row['lastname'].(!empty($row['firstname'])?(' '.mb_substr($row['firstname'], 0, 1).'.'):''),
                        'dtChange' => $row['dtChange'],
                    );
                    $table['id_model'.$row['id_model']]=$item;
                }
                $table['id_model'.$row['id_model']][$row['InsertedDate']]=$row['count'];
            }
            $NumRow = 1;
            foreach($table as $row=>$value){
//                var_dump($value);
//                die();
                $class = fmod($NumRow, 2) != 1 ? ("impair") : ("pair");
                $fixed_assets.='<tr class="'.$class.'">';
                $fixed_assets.='<td class="small_size" style="width: 60px">'.$value['line'].'</td>';
                $fixed_assets.='<td class="small_size" style="width: 60px">'.$value['kind_assets'].'</td>';
                $fixed_assets.='<td class="small_size" style="width: 60px">'.$value['trademark'].'</td>';
                $fixed_assets.='<td class="small_size" style="width: 80px">'.$value['model'].'</td>';
                $fixed_assets.='<td class="small_size" style="width: 80px">'.$value['description'].'</td>';
                $fixed_assets.='<td class="small_size" style="width: 50px">'.$value['year'].'</td>';
                $fixed_assets.='<td class="small_size" style="width: 50px"></td>';
                $fixed_assets.='<td class="small_size" style="width: 61px"></td>';
                foreach($Years as $year){
                    if(array_key_exists($year, $value))
                        $fixed_assets.='<td class="small_size" style="width: 44px; text-align: center">'.$value[$year].'</td>';
                    else
                        $fixed_assets.='<td class="small_size"></td>';
                }
//                $fixed_assets.='<td class="small_size" style="width: 80px">'.$value['PositiveResponse'].'</td>';
                $positive = $value['PositiveResponse'];
                $fixed_assets.='<td id="pos_resp'.$value['id_model'].'" class="small_size" style="width: 80px">'.(strlen(trim($positive))>10?(mb_substr(trim($positive), 0, 10).'...
                <img id="pos_resp'.$value['id_model'].'" onclick="preview(pos_resp'.$value['id_model'].');" style="vertical-align: middle" title="Передивитись" src="/dolibarr/htdocs/theme/eldy/img/object-more.png">'):trim($positive)).'</td>';
                $fixed_assets.='<td id="Lpos_resp'.$value['id_model'].'" style="display:none">'.trim($positive).'</td>';
                $negative = $value['NegativeResponse'];
//                $fixed_assets.='<td class="small_size" style="width: 80px">'.$value['NegativeResponse'].'</td>';
                $fixed_assets.='<td id="negative_resp'.$value['id_model'].'" class="small_size" style="width: 80px">'.(strlen(trim($negative))>10?(mb_substr(trim($negative), 0, 10).'...
                <img id="negative_resp'.$value['id_model'].'" onclick="preview(negative_resp'.$value['id_model'].');" style="vertical-align: middle" title="Передивитись" src="/dolibarr/htdocs/theme/eldy/img/object-more.png">'):trim($negative)).'</td>';
                $fixed_assets.='<td id="Lnegative_resp'.$value['id_model'].'" style="display:none">'.trim($negative).'</td>';

                $fixed_assets.='<td class="small_size" style="width: 70px">'.$value['contact'].'</td>';
                $dtChange =  new DateTime($value['dtChange']);
                $fixed_assets.='<td class="small_size">'.$dtChange->format('d.m.y').'</td>';
                $fixed_assets.='<td class="small_size" style="width: 71px"></td>';
                $fixed_assets.='<td class="small_size" style="width: 71px"></td>';
                $fixed_assets.='<td class="small_size" style="width: 20px"><img id="img_'.$value["id_model"].'" onclick="" style="vertical-align: middle" title="Редагувати" src="/dolibarr/htdocs/theme/eldy/img/edit.png"></td>';
                $fixed_assets.='</tr>';
                $NumRow++;
            }

            $fixed_assets.='</tbody>';
        }

        if(empty($fixed_assets)){
            $fixed_assets.='<tbody class="economic_indicators"><tr class="impair"><td id="emptyrow_fixed_assets" colspan="13" style="text-align: center; font-weight: bold; color: red">
                    ДАНІ ВІДСУТНІ
                </td></tr></tbody>';
        }
        include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/economic_inditacors/sale/fixed_assets.html';
    }
    public function saveitem(){
//        echo '<pre>';
//        var_dump($this);
//        echo '</pre>';
//        die();
        global $user, $db;
        if(empty($this->rowid)) {
            $sql='insert into llx_societe_economic_indicator (socid,line_active,kindassets,trademark,for_what,`count`,`year`,
              container,time_purchase,rate,time_purchase2,rate2,PositiveResponse,NegativeResponse,contact,active,id_usr,model,fx_count_un_meas,fx_conteiner_un_meas)
              values('.$this->socid.', '.$this->line_active.', '.$this->kindassets.', '.$this->trademark.',
              "'.trim($this->for_what).'", '.$this->count.', '.$this->year.', '.$this->container.',
              '.$this->time_purchase.', '.$this->rate.', '.$this->time_purchase2.', '.$this->rate2.',
              "'.trim($this->PositiveResponse).'", "'.trim($this->NegativeResponse).'", '.$this->contact.', 1, '.$user->id.', '.$this->model.', '.$this->UnMeasurement.', '.$this->ContainerUnMeasurement.')';
        }else{
            $sql='update llx_societe_economic_indicator set
            line_active = '.$this->line_active.', kindassets = '.$this->kindassets.', trademark = '.$this->trademark.',
            for_what = "'.trim($this->for_what).'", count = '.$this->count.', year = '.$this->year.', container = '.$this->container.',
            time_purchase = '.$this->time_purchase.', rate = '.$this->rate.', time_purchase2 = '.$this->time_purchase2.',
            rate2 = '.$this->rate2.', PositiveResponse = "'.trim($this->PositiveResponse).'", NegativeResponse = "'.trim($this->NegativeResponse).'",
            contact = '.$this->contact.', id_usr = '.$user->id.', model ='.$this->model.',fx_count_un_meas='.$this->UnMeasurement.'
            fx_conteiner_un_meas='.$this->ContainerUnMeasurement.'  where rowid = '.$this->rowid;
        }

        $res = $db->query($sql);
        if(!$res){
            var_dump($sql);
            dol_print_error($db);
        }
    }

    public function materials(){
        global $langs, $db;
        $materials='';
        $sql = "select `llx_c_model`.rowid as id_model, `llx_c_line_active`.line, `llx_c_kind_assets`.kind_assets, `llx_c_trademark`.trademark, `llx_c_model`.model, `llx_societe_economic_indicator`.`for_what`,
         round(`llx_societe_economic_indicator`.count, 0) `count`, `un_means`.`name` count_means, `container`, container_means.`name` as container_mean,
         `llx_societe_economic_indicator`.`time_purchase`, `llx_societe_economic_indicator`.`rate`,
         `llx_societe_economic_indicator`.`time_purchase2`, `llx_societe_economic_indicator`.`rate2`,
         `llx_societe_economic_indicator`.`PositiveResponse`,  `llx_societe_economic_indicator`.`NegativeResponse`,
         `llx_societe_contact`.`lastname`, `llx_societe_contact`.`firstname`, `llx_societe_economic_indicator`.`dtChange`
         from `llx_societe_economic_indicator`
        left join `llx_c_line_active` on `llx_c_line_active`.rowid=line_active
        left join `llx_c_kind_assets` on `llx_c_kind_assets`.rowid=kindassets
        left join `llx_c_trademark` on `llx_c_trademark`.rowid=`llx_societe_economic_indicator`.trademark
        left join `llx_c_model` on `llx_c_model`.rowid = `llx_societe_economic_indicator`.model
        left join `llx_societe_contact` on `llx_societe_contact`.rowid =  `llx_societe_economic_indicator`.contact
        left join `llx_c_measurement` as un_means on `un_means`.rowid=`llx_societe_economic_indicator`.`fx_count_un_meas`
        left join `llx_c_measurement` as container_means on `container_means`.rowid=`llx_societe_economic_indicator`.`fx_conteiner_un_meas`
        where `llx_societe_economic_indicator`.`socid`=".$this->socid."
        and `llx_societe_economic_indicator`.`active`= 1
        and `llx_c_line_active`.fx_type_indicator = 2
        order by line, kind_assets, trademark, `llx_c_model`.rowid, `llx_societe_economic_indicator`.`dtChange` desc";
//        die($sql);

        $restable = $db->query($sql);
        if(!$restable){
            var_dump($sql);
            dol_print_error($db);
        }
        $NumRow = 1;
        $materials='<tbody class="economic_indicators">';
        while($row = $db->fetch_object($restable)){
            $class = fmod($NumRow, 2) != 1 ? ("impair") : ("pair");
            $materials.='<tr class="'.$class.'">';
            $materials.='<td class="small_size" style="width: 60px">'.$row->line.'</td>';
            $materials.='<td class="small_size" style="width: 60px">'.$row->kind_assets.'</td>';
            $materials.='<td class="small_size" style="width: 60px">'.$row->trademark.'</td>';
            $materials.='<td class="small_size" style="width: 60px">'.$row->model.'</td>';
            $materials.='<td id="for_what'.$row->id_model.'" class="small_size" style="width: 60px">'.(strlen(trim($row->for_what))>8?(mb_substr(trim($row->for_what), 0, 5).'...
            <img id="m_for_what_prev'.$row->id_model.'" onclick="preview(m_for_what_prev'.$row->id_model.');" style="vertical-align: middle" title="Передивитись" src="/dolibarr/htdocs/theme/eldy/img/object-more.png">'):trim($row->for_what)).'</td>';
            $materials.='<td id="Lfor_what_prev'.$row->id_model.'" style="display:none">'.trim($row->for_what).'</td>';
            $materials.='<td class="small_size" style="width: 48px">'.$row->count.'</td>';
            $materials.='<td class="small_size" style="width: 41px">'.$row->count_means.'</td>';
            $materials.='<td class="small_size" style="width: 31px">'.round($row->container,0).'</td>';
            $materials.='<td class="small_size" style="width: 41px">'.$row->container_mean.'</td>';
            $materials.='<td class="small_size" style="width: 50px">'.$langs->trans('Month'.($row->time_purchase<10?('0'.$row->time_purchase):$row->time_purchase)).'</td>';
            $materials.='<td class="small_size" style="width: 50px">'.$row->rate.'</td>';
            $materials.='<td class="small_size" style="width: 50px">'.$langs->trans('Month'.($row->time_purchase2<10?('0'.$row->time_purchase2):$row->time_purchase2)).'</td>';
            $materials.='<td class="small_size" style="width: 48px">'.$row->rate2.'</td>';
//            $materials.='<td class="small_size" style="width: 80px">'.$row->PositiveResponse.'</td>';
//            $materials.='<td class="small_size" style="width: 80px">'.$row->NegativeResponse.'</td>';
            $materials.='<td id="pos_resp'.$row->id_model.'" class="small_size" style="width: 80px">'.(strlen(trim($row->PositiveResponse))>10?(mb_substr(trim($row->PositiveResponse), 0, 10).'...
            <img id="m_pos_resp_prev'.$row->id_model.'" onclick="preview(m_pos_resp_prev'.$row->id_model.');" style="vertical-align: middle" title="Передивитись" src="/dolibarr/htdocs/theme/eldy/img/object-more.png">'):trim($row->PositiveResponse)).'</td>';
            $materials.='<td id="Lpos_resp_prev'.$row->id_model.'" style="display:none">'.trim($row->PositiveResponse).'</td>';
//                $materials.='<td class="small_size" style="width: 80px">'.$value['NegativeResponse'].'</td>';
            $materials.='<td id="pos_resp'.$row->id_model.'" class="small_size" style="width: 80px">'.(strlen(trim($row->NegativeResponse))>10?(mb_substr(trim($row->NegativeResponse), 0, 10).'...
            <img id="m_negative_resp_prev'.$row->id_model.'" onclick="preview(m_negative_resp_prev'.$row->id_model.');" style="vertical-align: middle" title="Передивитись" src="/dolibarr/htdocs/theme/eldy/img/object-more.png">'):trim($row->NegativeResponse)).'</td>';
            $materials.='<td id="Lnegative_resp_prev'.$row->id_model.'" style="display:none">'.trim($row->NegativeResponse).'</td>';
            $materials.='<td class="small_size" style="width: 70px">'.$row->lastname.(!empty($row->firstname)?(' '.mb_substr($row->firstname, 0, 1).'.'):'').'</td>';
            $dtChange =  new DateTime($row->dtChange);
            $materials.='<td class="small_size">'.$dtChange->format('d.m.y').'</td>';
            $materials.='<td class="small_size" style="width: 71px"></td>';
            $materials.='<td class="small_size" style="width: 71px"></td>';
            $materials.='<td class="small_size" style="width: 20px"><img id="img_'.$row->id_model.'" onclick="" style="vertical-align: middle" title="Редагувати" src="/dolibarr/htdocs/theme/eldy/img/edit.png"></td>';
            $materials.='</tr>';
            $NumRow++;
        }
        $materials.='</tbody>';
        if(empty($materials)){
            $materials.='<tbody class="economic_indicators"><tr class="impair"><td id="emptyrow_materials" colspan="13" style="text-align: center; font-weight: bold; color: red">
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
    public function selectkind_assets($lineactive=0, $rowid=0){
//        var_dump($lineactive, $rowid);
//        die();
        global $db;
        $out = '<select id="KindAssets" name="KindAssets" class="combobox" size="1">';

        $out .='<option value="0" disabled="disabled" selected="selected">Вкажіть вид засобів</option>';
        if($lineactive==0){
            $out .= '</select>';
            return $out;
        }
        $sql = 'select rowid, kind_assets from llx_c_kind_assets where fx_line_active='.$lineactive.' and active = 1';
//        die($sql);
        $res = $db->query($sql);
        while($row = $db->fetch_object($res)){
            $out .='<option value = '.$row->rowid.' '.($rowid == $row->rowid?('selected="selected"'):'').'>'.$row->kind_assets.'</option>';
        }
        $out .= '</select>';
        return $out;
    }
    public function selectMeasurement($name='',$rowid=0){
//        die('test');
        global $db, $langs;
        $out = '<select id="'.$name.'" name="'.$name.'" class="combobox" size="1" style="width: 100px">';
        $out .='<option value="0" disabled="disabled" selected="selected">'.$langs->trans("UnitsOfMeasurement").'</option>';
        $sql = 'select rowid, name from llx_c_measurement where active = 1 order by name';

        $res = $db->query($sql);
        while($row = $db->fetch_object($res)){
            $out .='<option value = '.$row->rowid.' '.($rowid == $row->rowid?('selected="selected"'):'').'>'.$row->name.'</option>';
        }
        $out .= '</select>';
        return $out;
    }
    public function selecttrademark($rowid=0){
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
    public function selectmodel($trademark = 0, $kind_assets = 0, $rowid = 0){
        if($kind_assets == "null")$kind_assets=0;
        global $db, $langs;
        $out = '<select id="model" name="model" class="combobox" size="1">';
        $out .='<option value="0" disabled="disabled" selected="selected">'.$langs->trans("Model").'</option>';
        if(empty($trademark) && empty($kind_assets)){
            $out .= '</select>';
            return $out;
        }
        $sql = 'select rowid, model from `llx_c_model` where 1';
        if(!empty($trademark))
            $sql .= ' and fx_trademark = '.$trademark;
        if(!empty($kind_assets))
            $sql .= ' and fx_kind_assets = '.$kind_assets;
        $sql .= ' and active = 1';
        $res = $db->query($sql);
        while($row = $db->fetch_object($res)){
            $out .='<option value = '.$row->rowid.' '.($rowid == $row->rowid?('selected="selected"'):'').'>'.$row->model.'</option>';
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
    public function select_contact($htmlname='', $id=0){
        global $db;
        $out = '<select id="'.$htmlname.'" name="'.$htmlname.'" class="combobox" size="1">';
        $sql = 'select rowid, lastname,firstname from `llx_societe_contact` where `llx_societe_contact`.`socid`='.$this->socid.' and active = 1';
        $res = $db->query($sql);
        while($row = $db->fetch_object($res)){
            $out .='<option value = '.$row->rowid.' '.($id == $row->rowid?('selected="selected"'):'').'>'.trim($row->lastname).' '.mb_substr($row->firstname, 0,1).'</option>';
        }
        $out .= '</select>';
        return $out;
    }
    public function get_economic_indicators($line_active){
        global $db;
        $sql = 'select fx_type_indicator from llx_c_line_active where rowid='.$line_active;
        $res = $db->query($sql);
        $id = $db->fetch_object($res);
        return $id->fx_type_indicator;
    }
} 