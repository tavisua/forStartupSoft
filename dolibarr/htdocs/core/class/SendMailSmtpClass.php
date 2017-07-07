<?php
/**
* SendMailSmtpClass
* 
* Класс для отправки писем через SMTP с авторизацией
* Может работать через SSL протокол
* Тестировалось на почтовых серверах yandex.ru, mail.ru и gmail.com
* 
* @author Ipatov Evgeniy <admin@ipatov-soft.ru>
* @version 1.0
*/
class SendMailSmtpClass {

    /**
    *
    * @var string $smtp_username - логин
    * @var string $smtp_password - пароль
    * @var string $smtp_host - хост
    * @var string $smtp_from - от кого
    * @var integer $smtp_port - порт
    * @var string $smtp_charset - кодировка
    *
    */
    public $smtp_username;
    public $smtp_password;
    public $smtp_host;
    public $smtp_from;
    public $smtp_port;
    public $smtp_charset;

    public function __construct($smtp_username, $smtp_password, $smtp_host, $smtp_from, $smtp_port = 25, $smtp_charset = "utf-8") {
        $this->smtp_username = $smtp_username;
        $this->smtp_password = $smtp_password;
        $this->smtp_host = $smtp_host;
        $this->smtp_from = $smtp_from;
        $this->smtp_port = $smtp_port;
        $this->smtp_charset = $smtp_charset;
    }

    /**
    * Отправка письма
    *
    * @param string $mailTo - получатель письма
    * @param string $subject - тема письма
    * @param string $message - тело письма
    * @param string $headers - заголовки письма
    *
    * @return bool|string В случаи отправки вернет true, иначе текст ошибки    *
    */
    function send($mailTo, $subject, $message, $headers) {

        $contentMail = "Date: " . date("D, d M Y H:i:s") . " UT\r\n";
        $contentMail .= 'Subject: =?' . $this->smtp_charset . '?B?'  . base64_encode($subject) . "=?=\r\n";
        $contentMail .= $headers . "\r\n";
        $contentMail .= $message . "\r\n";
        
//        $socket = fsockopen("$this->smtp_host", 465, $errno, $errstr, 10);
//        if(!$socket)
//        {
//            echo "ERROR: $this->smtp_host 465 - $errstr ($errno)<br>\n";
//        }
//        else
//        {
//            echo "SUCCESS: $this->smtp_host 465 - ok<br>\n";
//        }

//        $socket = fsockopen("$this->smtp_host", 587, $errno, $errstr, 10);
//        if(!$socket)
//        {
//            echo "ERROR: $this->smtp_host 587 - $errstr ($errno)<br>\n";
//        }
//        else
//        {
//            echo "SUCCESS: $this->smtp_host 587 - ok<br>\n";
//        }
//        die();
        try {

            if(!$socket = fsockopen($this->smtp_host, $this->smtp_port, $errorNumber, $errorDescription, 30)){

                throw new Exception($errorNumber.".".$errorDescription);
            }
            var_dump(stream_get_contents($socket));
            die();
//
//            if (!$this->_parseServer($socket, "220")){
//                throw new Exception('Connection error');
//            }
//
			$server_name = $_SERVER["SERVER_NAME"];
            fputs($socket, "EHLO $server_name\r\n");
            if (!$this->_parseServer($socket, "250")) {
                fclose($socket);
                throw new Exception('Error of command sending: HELO');
            }

//            fputs($socket, "AUTH LOGIN\r\n");
//            if (!$this->_parseServer($socket, "334")) {
//                fclose($socket);
//                throw new Exception('Autorization error');
//            }
//
//
//
//            fputs($socket, base64_encode($this->smtp_username) . "\r\n");
//            if (!$this->_parseServer($socket, "334")) {
//                fclose($socket);
//                throw new Exception('Autorization error');
//            }
//
//            fputs($socket, base64_encode($this->smtp_password) . "\r\n");
//            if (!$this->_parseServer($socket, "235")) {
//                fclose($socket);
//                throw new Exception('Autorization error');
//            }
//
//            fputs($socket, "MAIL FROM: <".$this->smtp_username.">\r\n");
//            if (!$this->_parseServer($socket, "250")) {
//                fclose($socket);
//                throw new Exception('Error of command sending: MAIL FROM');
//            }
//
//			$mailTo = ltrim($mailTo, '<');
//			$mailTo = rtrim($mailTo, '>');
//            fputs($socket, "RCPT TO: <" . $mailTo . ">\r\n");
//            if (!$this->_parseServer($socket, "250")) {
//                fclose($socket);
//                throw new Exception('Error of command sending: RCPT TO');
//            }
//
//            fputs($socket, "DATA\r\n");
//            if (!$this->_parseServer($socket, "354")) {
//                fclose($socket);
//                throw new Exception('Error of command sending: DATA');
//            }
//
//            fputs($socket, $contentMail."\r\n.\r\n");
//            if (!$this->_parseServer($socket, "250")) {
//                fclose($socket);
//                throw new Exception("E-mail didn't sent");
//            }
//            var_dump($socket);
//            fputs($socket, "QUIT\r\n");
//            fclose($socket);
//            return true;
//            if (!($socket = fsockopen($this->smtp_username, $this->smtp_port, $errno, $errstr, 15)))
//            {
//                echo "Error connecting to '$this->smtp_host' ($errno) ($errstr)";
//            }

//            $this->server_parse($socket, '220');
//
//            fwrite($socket, 'EHLO '.$this->smtp_host."\r\n");
//            $this->server_parse($socket, '250');
//
//            fwrite($socket, 'AUTH LOGIN'."\r\n");
//            $this->server_parse($socket, '334');
//            var_dump(base64_encode($this->smtp_username));
//            die();
//            fwrite($socket, base64_encode($this->smtp_username)."\r\n");
//            $this->server_parse($socket, '334');
//
//            fwrite($socket, base64_encode($this->smtp_password)."\r\n");
//            $this->server_parse($socket, '235');
//
//            fwrite($socket, 'MAIL FROM: <'.$this->smtp_username.'>'."\r\n");
//            $this->server_parse($socket, '250');
//
////            foreach ($recipients as $email)
////            {
//                fwrite($socket, 'RCPT TO: <'.$mailTo.'>'."\r\n");
//                $this->server_parse($socket, '250');
////            }
//
//            fwrite($socket, 'DATA'."\r\n");
//            $this->server_parse($socket, '354');
//
//            fwrite($socket, 'Subject: '
//                .$subject."\r\n".'To: <'.$mailTo.'>'
//                ."\r\n".$headers."\r\n\r\n".$message."\r\n");
//
//            fwrite($socket, '.'."\r\n");
//            $this->server_parse($socket, '250');

            fwrite($socket, 'QUIT'."\r\n");
            fclose($socket);

            return true;
        } catch (Exception $e) {
            return  $e->getMessage();
        }
    }
    
    private function server_parse($socket, $response) {
//        while (@substr($responseServer, 3, 1) != ' ') {
//            if (!($responseServer = fgets($socket, 256))) {
//                return false;
//            }
//        }
//        if (!(substr($responseServer, 0, 3) == $response)) {
//            return false;
//        }
//        return true;
        $server_response = '';
        while (substr($server_response, 3, 1) != ' ')
        {
            if (!($server_response = fgets($socket, 256)))
            {
                var_dump($server_response);
                die();
                echo 'Error while fetching server response codes.', __FILE__, __LINE__, '</br>';
                return false;
            }
        }

        if (!(substr($server_response, 0, 3) == $response))
        {
            echo 'Unable to send e-mail."'.$server_response.'"', __FILE__, __LINE__;
            return false;
        }
        return true;
    }
}