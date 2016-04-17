<?php

/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 12.04.2016
 * Time: 20:03
 */
class proposedProducts
{
    var $rowid;
    var $fx_proposition;
    var $fx_category;
    var $Prodaction;
    var $ProductName;
    var $articul;
    var $Number1C;
    var $Nal;
    var $ed_izm;
    var $shipTown;
    var $featureOffers;
    var $profitCustomer;
    var $price;
    var $offerPrice;
    var $advance;
    var $deadlineAdvance;
    var $deadlineSale;
    var $dateExec;
    var $delivary;
    var $otherDiscont;
    var $description;
	function __construct($db)
	{
		global $langs, $db,$user, $conf;

		$this->db = $db;
        $this->user = $user;
        $this->conf = $conf;
        $this->langs = $langs;
	}
    function add(){
        $sql = 'insert into llx_proposition_product (fx_proposition,fx_category,Prodaction,
                ProductName,articul,Number1C,Nal,ed_izm,shipTown,featureOffers,
                profitCustomer,price,offerPrice,advance,deadlineAdvance,deadlineSale,
                dateExec,delivary,otherDiscont,description,active,id_usr) values(';
        $sql .= $this->fx_proposition.',';
        $sql .= $this->fx_category.',';
        $sql .= empty($this->Prodaction)?"null":("'".$this->db->escape(trim($this->Prodaction))."'").',';
        $sql .= empty($this->ProductName)?"null":("'".$this->db->escape(trim($this->ProductName))."'").',';
        $sql .= empty($this->articul)?"null":("'".$this->db->escape(trim($this->articul))."'").',';
        $sql .= empty($this->Number1C)?"null":("'".$this->db->escape(trim($this->Number1C))."'").',';
        $sql .= empty($this->Nal)?"null":("'".$this->db->escape($this->Nal)."'").',';
        $sql .= empty($this->ed_izm)?"null":("'".$this->db->escape(trim($this->ed_izm))."'").',';
        $sql .= empty($this->shipTown)?"null":("'".$this->db->escape(trim($this->shipTown))."'").',';
        $sql .= empty($this->featureOffers)?"null":("'".$this->db->escape(trim($this->featureOffers))."'").',';
        $sql .= empty($this->profitCustomer)?"null":("'".$this->db->escape(trim($this->profitCustomer))."'").',';
        $sql .= empty($this->price)?"null":("'".$this->db->escape($this->price)."'").',';
        $sql .= empty($this->offerPrice)?"null":("'".$this->db->escape($this->offerPrice)."'").',';
        $sql .= empty($this->advance)?"null":("'".$this->db->escape($this->advance)."'").',';
        $sql .= empty($this->deadlineAdvance)?"null":("'".$this->db->escape(trim($this->deadlineAdvance))."'").',';
        $sql .= empty($this->deadlineSale)?"null":("'".$this->db->escape(trim($this->deadlineSale))."'").',';
        $sql .= empty($this->dateExec)?"null":("'".$this->db->escape(trim($this->dateExec))."'").',';
        $sql .= empty($this->delivary)?"null":("'".$this->db->escape(trim($this->delivary))."'").',';
        $sql .= empty($this->otherDiscont)?"null":("'".$this->db->escape(trim($this->otherDiscont))."'").',';
        $sql .= empty($this->description)?"null":("'".$this->db->escape(trim($this->description))."'");
        $sql.=', 1, '.$this->user->id.')';
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
    }
    function update(){
        $sql = 'update llx_proposition_product set ';
        $sql .="fx_proposition=".$this->fx_proposition.",";
        $sql .="fx_category=".$this->fx_category.",";
        $sql .="Prodaction=".(empty($this->Prodaction)?"null":("'".trim($this->Prodaction)."'")).",";
        $sql .="ProductName=".(empty($this->ProductName)?"null":("'".trim($this->ProductName)."'")).",";
        $sql .="articul=".(empty($this->articul)?"null":("'".trim($this->articul)."'")).",";
        $sql .="Number1C=".(empty($this->Number1C)?"null":("'".trim($this->Number1C)."'")).",";
        $sql .="Nal=".(empty($this->Nal)?"null":$this->Nal).",";
        $sql .="ed_izm=".(empty($this->ed_izm)?"null":("'".trim($this->ed_izm)."'")).",";
        $sql .="shipTown=".(empty($this->shipTown)?"null":("'".trim($this->shipTown)."'")).",";
        $sql .="featureOffers=".(empty($this->featureOffers)?"null":("'".trim($this->featureOffers)."'")).",";
        $sql .="profitCustomer=".(empty($this->profitCustomer)?"null":("'".trim($this->profitCustomer)."'")).",";
        $sql .="price=".(empty($this->price)?"null":$this->price).',';
        $sql .="offerPrice=".(empty($this->offerPrice)?"null":$this->offerPrice).',';
        $sql .="advance=".(empty($this->advance)?"null":$this->advance).',';
        $sql .="deadlineAdvance=".(empty($this->deadlineAdvance)?"null":("'".trim($this->deadlineAdvance)."'")).",";
        $sql .="deadlineSale=".(empty($this->deadlineSale)?"null":("'".trim($this->deadlineSale)."'")).",";
        $sql .="dateExec=".(empty($this->dateExec)?"null":("'".trim($this->dateExec)."'")).",";
        $sql .="delivary=".(empty($this->delivary)?"null":("'".trim($this->delivary)."'")).",";
        $sql .="otherDiscont=".(empty($this->otherDiscont)?"null":("'".trim($this->otherDiscont)."'")).",";
        $sql .="description=".(empty($this->description)?"null":("'".trim($this->description)."'")).",";
        $sql .="active=1,";
        $sql .="id_usr=".$this->user->id.' ';
        $sql .="where rowid=".$this->rowid;
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
    }
    function fetch($rowid){
        $sql = 'select `begin`,`end`,`llx_c_lineactive_customer`.`name` as lineactive,`llx_post`.`postname`,`text` as title,`description`
            from `llx_c_proposition`
            left join `llx_c_lineactive_customer` on `llx_c_lineactive_customer`.`rowid` = `llx_c_proposition`.`fk_lineactive`
            left join `llx_post` on `llx_post`.`rowid` = `llx_c_proposition`.`fk_post`
            where llx_c_proposition.rowid = '.$rowid;
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $obj = $this->db->fetch_object($res);
        return $obj;
    }
    function ShowProducts($prodosed_id, $preview = false){
        $sql = "select rowid,Prodaction,ProductName,articul,Number1C,Nal,ed_izm,
            shipTown,featureOffers,profitCustomer,price,offerPrice,advance,deadlineAdvance,
            deadlineSale,dateExec,delivary,otherDiscont,description from llx_proposition_product
            where llx_proposition_product.fx_proposition=".$prodosed_id." and active = 1";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $out = '<tbody>';
        if($this->db->num_rows($res) == 0){
            $out .= '<tr class="pair">';
            for($i=0;$i<19;$i++) {
                if($i<=1)
                    $class='class="middle_size"';
                elseif($i>1&&$i<=8)
                    $class='class="middle_size pageCol basicInformation"';
                elseif($i>8&&$i<16)
                    $class='class="middle_size pageCol priceOffers" style="display:none"';
                elseif($i>=16&&$i<18)
                    $class='class="middle_size pageCol otherOffersInformation" style="display:none"';
                else
                    $class='';

                $out .= '<td ' . $class . '>&nbsp;</td>';
            }
            $out .= '</tr>';
        }else{
            $num = 0;
            while($obj = $this->db->fetch_array($res)){
                $keys = array_keys($obj);
                fmod($num,2) == 0?$class="pair":$class="impair";
                $out .= '<tr class = "'.$class.'" id="'.$obj[$keys[1]].'">';
                $i=2;
                $td_num = 0;
                while($i<count($keys)){
                    if(!is_numeric($keys[$i])) {
                        if($td_num<=1)
                            $cell_class='class="middle_size"';
                        elseif($td_num>1&&$td_num<=8)
                            $cell_class='class="middle_size pageCol basicInformation"';
                        elseif($td_num>8&&$td_num<16)
                            $cell_class='class="middle_size pageCol priceOffers" style="display:none"';
                        elseif($td_num>=16&&$td_num<18)
                            $cell_class='class="middle_size pageCol otherOffersInformation" style="display:none"';
                        else
                            $cell_class='';
                        $out .= '<td id="'.$keys[$i].$obj[$keys[1]].'" '.$cell_class.'>'.(mb_strlen($obj[$keys[$i]], 'UTF-8')>20?
                                mb_substr($obj[$keys[$i]], 0, 20, 'UTF-8').'...<input id="_'.$keys[$i].$obj[$keys[1]].'" type=hidden value="'.$obj[$keys[$i]].'">':$obj[$keys[$i]]).'</td>';
                        $td_num++;
                    }
                    $i++;
                }
                //control_button
                $out.= '<td>';
                if(!$preview) {
                    $out .= '<img  id="img_edit' . $obj[$keys[1]] . '" src="' . DOL_URL_ROOT . '/theme/' . $this->conf->theme . '/img/edit.png" title="' . $this->langs->trans('Edit') . '" style="vertical-align: middle" onclick="edit_item(' . $obj[$keys[1]] . ');">&nbsp;&nbsp;';
                    $out .= '<img  id="img_del' . $obj[$keys[1]] . '" src="' . DOL_URL_ROOT . '/theme/' . $this->conf->theme . '/img/delete.png" title="' . $this->langs->trans('Del') . '" style="vertical-align: middle" onclick="del_item(' . $obj[$keys[1]] . ');">';
                }else{
                    $out .= '<img  id="img_rev' . $obj[$keys[1]] . '" src="' . DOL_URL_ROOT . '/theme/' . $this->conf->theme . '/img/preview.png" title="' . $this->langs->trans('review') . '" style="vertical-align: middle" onclick="show_item(' . $obj[$keys[1]] . ');">&nbsp;&nbsp;';
                    $out .= '<img  id="img_sel' . $obj[$keys[1]] . '" src="' . DOL_URL_ROOT . '/theme/' . $this->conf->theme . '/img/uncheck.png" title="' . $this->langs->trans('Choose') . '" style="vertical-align: middle" onclick="select_item($(this),' . $obj[$keys[1]] . ');">';
                }
                $out.='</td>';
                $out .= '</tr>';
                $num++;
            }
        }
        $out .='</tbody>';
        return $out;
    }
    function fetchProductsItem($rowid){
        $sql = "select rowid,fx_proposition,fx_category,Prodaction,ProductName,articul,Number1C,Nal,ed_izm,
            shipTown,featureOffers,profitCustomer,price,offerPrice,advance,deadlineAdvance,
            deadlineSale,dateExec,delivary,otherDiscont,description from llx_proposition_product
            where rowid=".$rowid;
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $obj = $this->db->fetch_object($res);
        $this->rowid = $obj->rowid;
        $this->fx_proposition = $obj->fx_proposition;
        $this->fx_category = $obj->fx_category;
        $this->Prodaction = $obj->Prodaction;
        $this->ProductName = $obj->ProductName;
        $this->articul = $obj->articul;
        $this->Number1C = $obj->Number1C;
        $this->Nal = $obj->Nal;
        $this->ed_izm = $obj->ed_izm;
        $this->shipTown = $obj->shipTown;
        $this->featureOffers = $obj->featureOffers;
        $this->profitCustomer = $obj->profitCustomer;
        $this->price = $obj->price;
        $this->offerPrice = $obj->offerPrice;
        $this->advance = $obj->advance;
        $this->deadlineAdvance = $obj->deadlineAdvance;
        $this->deadlineSale = $obj->deadlineSale;
        $this->dateExec = $obj->dateExec;
        $this->delivary = $obj->delivary;
        $this->otherDiscont = $obj->otherDiscont;
        $this->description = $obj->description;
    }
}