<?php

/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 29.01.2016
 * Time: 16:35
 */
class finance_class{
    var $fx_counter_party;  //з якою категорією працює
    var $fx_socid;          //ИД контрагента
    var $address;           //адреса
    var $account_number;     //Р/р
    var $fx_account_currency;       //Валюта рахунку
    var $mfo;               //МФО
    var $fx_finance_service;//фінансові послуги
    var $size_finance_service;//розмір фін.послуг
    var $fx_currency_finance_service;//валюта фінансування
    var $description;       //примітка
    var $finance_service_comment;   //коментар по работі з установою
    var $comment_about_customer;    //коментар установи про клієнта/контрагента
    var $info_finance_service_about_customer;//інформація від фінустанови про клієнта
    var $erdpo;             //ЄРДПО
    var $inn;               //ІНН
    var $number_of_licence; //номер свідоцтва

    public function __construct($socid=0)
    {
        $this->socid = $socid;
    }
    public function add(){

    }



}