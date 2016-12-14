<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 18.11.2016
 * Time: 10:05
 */
// пример использования
require_once "SendMailSmtpClass.php"; // подключаем класс

$mailSMTP = new SendMailSmtpClass('ipatovsoft@mail.ru', '****', 'ssl://smtp.mail.ru', 'Evgeniy', 465); // создаем экземпляр класса
// $mailSMTP = new SendMailSmtpClass('логин', 'пароль', 'хост', 'имя отправителя');

// заголовок письма
$headers= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=utf-8\r\n"; // кодировка письма
$headers .= "From: Evgeniy <ipatovsoft@mail.ru>\r\n"; // от кого письмо !!! тут e-mail, через который происходит авторизация
$result =  $mailSMTP->send('zhenikipatov@yandex.ru', 'Тема письма', 'Текст письма', $headers); // отправляем письмо
// $result =  $mailSMTP->send('Кому письмо', 'Тема письма', 'Текст письма', 'Заголовки письма');
if($result === true){
    echo "Письмо успешно отправлено";
}else{
    echo "Письмо не отправлено. Ошибка: " . $result;
}