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
    var $tech_param;
    var $productivity;
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
    var $CategoryResponse;
    var $Response;

    public function __construct($socid=0)
    {
        $this->socid = $socid;
    }
    public function fetch_fixed_assets($rowid){
        global $db;
//        echo '<pre>';
//        var_dump($_REQUEST);
//        echo '</pre>';
//        die();
        $sql = "select socid,line_active,kindassets,trademark,model,for_what,count,fx_count_un_meas,year,tech_param,productivity,
        container,fx_conteiner_un_meas,time_purchase,rate,time_purchase2,rate2,CategoryResponse,Response,PositiveResponse,NegativeResponse,contact
        from llx_societe_economic_indicator where rowid = ".$rowid;
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        $obj = $db->fetch_object($res);
        $this->rowid = $rowid;
        $this->socid = $obj->socid;
        $this->line_active = $obj->line_active;
        $this->kindassets = $obj->kindassets;
        $this->trademark = $obj->trademark;
        $this->for_what = $obj->for_what;
        $this->count = $obj->count;
        $this->year = $obj->year;
        $this->tech_param = $obj->tech_param;
        $this->productivity = $obj->productivity;
        $this->container = $obj->container;
        $this->time_purchase = $obj->time_purchase;
        $this->rate = $obj->rate;
        $this->time_purchase2 = $obj->time_purchase2;
        $this->rate2 = $obj->rate2;
        $this->PositiveResponse = $obj->PositiveResponse;
        $this->NegativeResponse = $obj->NegativeResponse;
        $this->Response = $obj->Response;
        $this->CategoryResponse = $obj->CategoryResponse;
        $this->contact = $obj->contact;
        $this->model = $obj->model;
        $this->UnMeasurement = $obj->fx_count_un_meas;
        $this->ContainerUnMeasurement = $obj->fx_conteiner_un_meas;
    }
    public function fixed_assets(){
        global $langs, $db, $conf;
        if(empty($this->socid)){
            $this->fetch_fixed_assets($_REQUEST['socid']);
        }
        $YearCount = 0;
        $sql = "select distinct DATE_FORMAT(`llx_societe_economic_indicator`.`dtChange`, '%Y') as Date
            from `llx_societe_economic_indicator`
            left join `llx_c_line_active` on `llx_c_line_active`.rowid=line_active
            where `llx_societe_economic_indicator`.`socid` = '.$this->socid.'
            and `llx_c_line_active`.fx_type_indicator = 1
            and `llx_societe_economic_indicator`.`active`= 1 order by `Date`";
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

        $sql = "select `llx_societe_economic_indicator`.`rowid`, `llx_c_model`.rowid as id_model, `llx_c_line_active`.line, `llx_c_kind_assets`.kind_assets, `llx_c_trademark`.trademark, `llx_c_model`.model, `llx_c_model`.description,
        `llx_c_model`.description_1,`llx_c_model`.description_2,
         `llx_societe_economic_indicator`.`year`, `llx_societe_economic_indicator`.`PositiveResponse`, `llx_societe_economic_indicator`.`Response`, `llx_societe_economic_indicator`.`CategoryResponse`,`llx_societe_economic_indicator`.`NegativeResponse`,
         `llx_societe_contact`.`lastname`, `llx_societe_contact`.`firstname`, DATE_FORMAT(`llx_societe_economic_indicator`.`dtChange`, '%Y') as `InsertedDate`,
         round(`llx_societe_economic_indicator`.count, 0) count, `llx_societe_economic_indicator`.`dtChange`, 
         case when `llx_societe_economic_indicator`.`tech_param` = 'null' then `llx_c_model`.`basic_param` else `llx_societe_economic_indicator`.`tech_param` end as tech_param, 
         case when `llx_societe_economic_indicator`.`productivity` = 'null' then `llx_c_model`.`productivity` else `llx_societe_economic_indicator`.`productivity` end as productivity
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
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();

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
//                if($row['rowid'] == 95) {
//                    echo '<pre>';
//                    var_dump($row);
//                    echo '</pre>';
//                    die();
//                }
//                if(!in_array('rowid'.$row['rowid'], $table)) {

                    $item = array('id_model' => $row['id_model'],
                        'line' => $row['line'],
                        'rowid' => $row['rowid'],
                        'kind_assets' => $row['kind_assets'],
                        'trademark' => $row['trademark'],
                        'model' => $row['model'],
                        'description' => $row['description'],
                        'description_1' => $row['description_1'],
                        'description_2' => $row['description_2'],
                        'year' => $row['year'],
                        'tech_param' => $row['tech_param'],
                        'productivity' => $row['productivity'],
                        'PositiveResponse' => $row['PositiveResponse'],
                        'Response' => $row['Response'],
                        'NegativeResponse' => $row['NegativeResponse'],
                        'count' => $row['count'],
                        'contact' => $row['lastname'].(!empty($row['firstname'])?(' '.mb_substr($row['firstname'], 0, 1, 'UTF-8').'.'):''),
                        'dtChange' => $row['dtChange'],
                    );
                    $table[]=$item;
//                }
//                $table['id_model'.$row['id_model']][$row['InsertedDate']]=$row['count'];
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
                $fixed_assets.='<td class="small_size" style="width: 80px">'.($value['description']!='-'?' '.$value['description']:'').($value['description_1']!='-'?' '.$value['description_1']:'').
                    ($value['description_2']!='-'?' '.$value['description_2']:'').'</td>';
                $fixed_assets.='<td class="small_size" style="width: 50px">'.$value['year'].'</td>';
                $tech_param = $value['tech_param'];
                $fixed_assets.='<td onclick="preview(tech_param'.$value['rowid'].');" id="tech_param'.$value['rowid'].'" class="small_size" style="width: 50px">'.(strlen(trim($tech_param))>3?mb_substr(trim($tech_param), 0, 3, 'UTF-8').'...':trim($tech_param)).'</td>';
                $fixed_assets.='<td id="Ltech_param'.$value['rowid'].'" style="display:none">'.trim($tech_param).'</td>';
                $productivity = $value['productivity'];
                $fixed_assets.='<td onclick="preview(productivity'.$value['rowid'].');" id="productivity'.$value['rowid'].'" class="small_size" style="width: 60px">'.(strlen(trim($productivity))>3?mb_substr(trim($productivity), 0, 3, 'UTF-8').'...':trim($productivity)).'</td>';
                $fixed_assets.='<td id="Lproductivity'.$value['rowid'].'" style="display:none">'.trim($productivity).'</td>';                
//                $fixed_assets.='<td class="small_size" style="width: 61px">'.$value['productivity'].'</td>';
                foreach($Years as $year){
                    if(array_key_exists($year, $value))
                        $fixed_assets.='<td class="small_size" style="width: 44px; text-align: center">'.$value[$year].'</td>';
                    else
                        $fixed_assets.='<td class="small_size"></td>';
                }
//                $fixed_assets.='<td class="small_size" style="width: 80px">'.$value['PositiveResponse'].'</td>';
//                echo '<pre>';
//                var_dump($value);
//                echo '</pre>';
//                die();
                $positive = $value['Response'];
                switch ($value['Response']){
                    case -1:{
                        $color = "red";
                    }break;
                    case 0:{
                        $color = "yellow";
                    }break;
                    case 1:{
                        $color = "green";
                    }break;
                }
                $fixed_assets.='<td id="resp'.$value['id_model'].'" class="small_size" style="width: 160px;background-color:'.$color.';">'.(mb_strlen(trim($positive))>10?(mb_substr(trim($positive), 0, 22, 'UTF-8').'...
                <img id="resp'.$value['id_model'].'" onclick="preview(resp'.$value['id_model'].');" style="vertical-align: middle" title="Передивитись" src="/dolibarr/htdocs/theme/eldy/img/object-more.png">'):trim($positive)).'</td>';
                $fixed_assets.='<td id="Lpos_resp'.$value['id_model'].'" style="display:none">'.trim($positive).'</td>';
//                $negative = $value['NegativeResponse'];
//                $fixed_assets.='<td class="small_size" style="width: 80px">'.$value['NegativeResponse'].'</td>';
//                $fixed_assets.='<td id="negative_resp'.$value['id_model'].'" class="small_size" style="width: 80px">'.(strlen(trim($negative))>10?(mb_substr(trim($negative), 0, 10).'...
//                <img id="negative_resp'.$value['id_model'].'" onclick="preview(negative_resp'.$value['id_model'].');" style="vertical-align: middle" title="Передивитись" src="/dolibarr/htdocs/theme/eldy/img/object-more.png">'):trim($negative)).'</td>';
//                $fixed_assets.='<td id="Lnegative_resp'.$value['id_model'].'" style="display:none">'.trim($negative).'</td>';

                $fixed_assets.='<td class="small_size" style="width: 70px">'.$value['contact'].'</td>';
                $dtChange =  new DateTime($value['dtChange']);
                $fixed_assets.='<td class="small_size">'.$dtChange->format('d.m.y').'</td>';
                $fixed_assets.='<td class="small_size" style="width: 71px"></td>';
                $fixed_assets.='<td class="small_size" style="width: 71px"></td>';
                $fixed_assets.='<td class="small_size" style="width: 20px"><img id="img_'.$value["id_model"].'" onclick="EditItem('.$value['rowid'].')" style="vertical-align: middle" title="Редагувати" src="/dolibarr/htdocs/theme/eldy/img/edit.png"></td>';
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
            $sql='insert into llx_societe_economic_indicator (socid,line_active,kindassets,trademark,for_what,`count`,`year`,`tech_param`,productivity,
              container,time_purchase,rate,time_purchase2,rate2,PositiveResponse,NegativeResponse,CategoryResponse,Response,contact,active,id_usr,model,fx_count_un_meas,fx_conteiner_un_meas)
              values('.$this->socid.', '.(empty($this->line_active)?"null":$this->line_active).', '.
                (empty($this->kindassets)?"null":$this->kindassets).', '.
                (empty($this->trademark)?"null":$this->trademark).',"'.trim($this->for_what).'", '.
                (empty($this->count)?"null":$this->count).', '.(empty($this->year)?"null":$this->year).', '.
                (empty($this->tech_param)?"null":"'".$db->escape($this->tech_param)."'").', '.
                (empty($this->productivity)?"null":$this->productivity).', '.
                (empty($this->container)?"null":$this->container).','.(empty($this->time_purchase)?"null":$this->time_purchase).', '.
                (empty($this->rate)?"null":$this->rate).', '.(empty($this->time_purchase2)?"null":$this->time_purchase2).', '.
                (empty($this->rate2)?"null":$this->rate2).',
              "'.trim($this->PositiveResponse).'", "'.trim($this->NegativeResponse).'",'.$this->CategoryResponse.', "'.trim($this->Response).'", '.(empty($this->contact)?"null":$this->contact).', 1, '.$user->id.', '.
                (empty($this->model)?"null":$this->model).', '.(empty($this->UnMeasurement)?"null":$this->UnMeasurement).', '.(empty($this->ContainerUnMeasurement)?"null":$this->ContainerUnMeasurement).')';
        }else{
            $sql='update llx_societe_economic_indicator set
            line_active = '.$this->line_active.',
            kindassets = '.(empty($this->kindassets)?"null":$this->kindassets).',
            trademark = '.(empty($this->trademark)?"null":$this->trademark).",
            for_what = '".trim($this->for_what)."',
            `count` = ".(empty($this->count)?"null":$this->count).',
            `year` = '.(empty($this->year)?"null":$this->year).',
            `tech_param` = '.(empty($this->tech_param)?"null":"'".$db->escape($this->tech_param)."'").',
            `productivity` = '.(empty($this->productivity)?"null":"'".$this->productivity."'").',
            container = '.(empty($this->container)?"null":$this->container).',
            time_purchase = '.(empty($this->time_purchase)?"null":$this->time_purchase).',
            rate = '.(empty($this->rate)?"null":$this->rate).',
            time_purchase2 = '.(empty($this->time_purchase2)?"null":$this->time_purchase2).',
            rate2 = '.(empty($this->rate2)?"null":$this->rate2).",
            PositiveResponse = '".trim($this->PositiveResponse)."',
            NegativeResponse = '".trim($this->NegativeResponse)."',
            CategoryResponse = '".trim($this->CategoryResponse)."',
            Response = '".trim($this->Response)."',
            contact = ".(empty($this->contact)?"null":$this->contact).',
            id_usr = '.$user->id.',
            model ='.(empty($this->model)?"null":$this->model).',
            fx_count_un_meas='.(empty($this->UnMeasurement)?"null":$this->UnMeasurement).',
            fx_conteiner_un_meas='.(empty($this->ContainerUnMeasurement)?"null":$this->ContainerUnMeasurement).'  where rowid = '.$this->rowid;
        }
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
        $res = $db->query($sql);
        if(!$res){
            llxHeader();
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
        $sql = 'select rowid, LCASE (line)line from llx_c_line_active where active = 1 order by line collate utf8_unicode_ci';
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
        $sql = 'select rowid, kind_assets from llx_c_kind_assets where fx_line_active='.$lineactive.' and active = 1 order by kind_assets collate utf8_unicode_ci';
//        die($sql);
        $res = $db->query($sql);
        while($row = $db->fetch_object($res)){
            $out .='<option value = '.$row->rowid.' '.($rowid == $row->rowid?('selected="selected"'):'').'>'.$row->kind_assets.'</option>';
        }
        $out .= '</select>';
        return $out;
    }
    public function selecttare($name='',$rowid=0){
//        die('test');
        global $db, $langs;
        $out = '<select id="'.$name.'" name="'.$name.'" class="combobox" size="1" style="width: 100px">';
        $out .='<option value="0" disabled="disabled" selected="selected">'.$langs->trans("Tare").'</option>';
        $sql = 'select rowid, name from llx_c_tare where active = 1 order by name';

        $res = $db->query($sql);
        while($row = $db->fetch_object($res)){
            $out .='<option value = '.$row->rowid.' '.($rowid == $row->rowid?('selected="selected"'):'').'>'.$row->name.'</option>';
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
    //
    public function selectmodel($trademark = 0, $kind_assets = 0, $rowid = 0, $lineactive = ''){

        if($kind_assets == "null")$kind_assets=0;
        global $db, $langs;
        $out = '<select id="model" name="model" class="combobox" size="1">';
        $out .='<option value="0" disabled="disabled" selected="selected">'.$langs->trans("Model").'</option>';
        if(empty($trademark) && empty($kind_assets) && empty($lineactive)){
            $out .= '</select>';
            return $out;
        }
        $sql = 'select rowid, model,fx_trademark,fx_kind_assets,basic_param, description, description_1, description_2 from `llx_c_model` where 1';
        if(!empty($trademark))
            $sql .= ' and fx_trademark = '.$trademark;
        if(!empty($lineactive) && empty($kind_assets)) {
            $tmp = "select rowid from llx_c_kind_assets where fx_line_active = ".$lineactive." and active = 1";
            $res_tmp = $db->query($tmp);
            if(!$res_tmp)
                dol_print_error($db);
            $kind_assets = array();
            while($kind_asset = $db->fetch_array($res_tmp)){
                $kind_assets[] = $kind_asset['rowid'];
            }
            $sql .= ' and fx_kind_assets in (' . implode(',',$kind_assets).')';
        }
        if(!empty($kind_assets)&&!is_array($kind_assets))
            $sql .= ' and fx_kind_assets = '.$kind_assets;
        $sql .= ' and active = 1';
        $sql .= ' order by model collate utf8_unicode_ci';
        $res = $db->query($sql);
//        die($sql);
        if(!$res)
            dol_print_error($db);
        while($row = $db->fetch_object($res)){
            $tmp = $row->model.$row->basic_param.$row->description;
            if(!empty($tmp))
                $out .='<option trademark = "'.$row->fx_trademark.'" kind_assets = "'.$row->fx_kind_assets.'" value = '.$row->rowid.' '.($rowid == $row->rowid?('selected="selected"'):'').'>'.$row->model.' ('.$row->basic_param.' '.$row->description.(empty($row->description_1)?'':' '.$row->description_1).(empty($row->description_2)?'':' '.$row->description_2).')</option>';
        }
        $out .= '</select>';
        return $out;
    }
    public function select_category($id = 0){
//        return $id;
        $out = '<select size="1" id="CategoryResponse" name="CategoryResponse" class="combobox">';
        $out .= '<option value="0" '.($id == 0?'selected="selected"':'').'>Нейтральні</option>';
        $out .= '<option value="-1" '.($id == -1?'selected="selected"':'').' >Негативні</option>';
        $out .= '<option value="1" '.($id == 1?'selected="selected"':'').' >Позитивні</option>';
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
            $out .='<option value = '.$row->rowid.' '.($id == $row->rowid?('selected="selected"'):'').'>'.trim($row->lastname).' '.mb_substr($row->firstname, 0,1, 'UTF-8').'</option>';
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