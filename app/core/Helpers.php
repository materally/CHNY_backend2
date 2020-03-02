<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class KM_Helpers {

    public function createSlug($string, $wordLimit = 0)
    {
        $separator = '-';
        
        if($wordLimit != 0){
            $wordArr = explode(' ', $string);
            $string = implode(' ', array_slice($wordArr, 0, $wordLimit));
        }
    
        $quoteSeparator = preg_quote($separator, '#');
    
        $trans = array(
            '&.+?;'                     => '',
            '[^\w\d _-]'                => '',
            '\s+'                       => $separator,
            '('.$quoteSeparator.')+'    => $separator
        );
    
        $string = strip_tags($string);
        foreach ($trans as $key => $val){
            $string = preg_replace('#'.$key.'#iu', $val, $string);
        }
    
        $string = strtolower($string);
    
        $what     = array('á', 'é', 'í', 'ó', 'ö', 'ő', 'ú', 'ü', 'ű', 'Á', 'É', 'Í', 'Ó', 'Ö', 'Ő', 'Ú', 'Ü', 'Ű');
        $forwhat  = array('a', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'u', 'a', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'u');
        
        $string = str_replace($what, $forwhat, $string);
    
        return trim(trim($string, $separator));
    }

    public function shortString($string, $length)
    {
        if (strlen($string) >= $length) {
            return mb_substr($string, 0, $length, "utf-8"). " ... ";
        }else{
            return $string;
        }
    }

    public function nicePrice($number)
    {
        return number_format($number, 0, ',', ' ') . ' Ft';
    }

    public function generateToken($i = 8)
    {
        return bin2hex(random_bytes($i));
    }

    public function generatePassword($length = 5)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars),0,$length);
    }

    public function imageOrNot($url)
    {
        return (empty($url)) ? SITE_URL_PUBLIC."/assets/img/no_image.jpg" : $url;
    }

    public function sendEmail($to, $subject, $content, $is_html = false, $template = false, $data = [], $attachment = false)
    {
        // USE: $this->sendEmail("kulcsarmate@gmail.com", "TÁRGY", "", true, "uj_vendeg", ['password' => 'ASDQER', 'reszletek' => 'Ezek lesznek a részletek!']);
        if($is_html AND $template !== false){
            $content = $this->load_email_template($template);
            if(!empty($data)){
                foreach ($data as $key => $value) {
                    $content = str_replace("{".$key."}", $value, $content);
                }
            }
        }
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host     = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        //$mail->SMTPDebug = 4; // Hiba esetén kiíratni!
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->CharSet = 'UTF-8';
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->From     = SMTP_FROM_EMAIL;
        $mail->FromName = SMTP_FROM_NAME;
        $mail->AddAddress($to);
        if(SMTP_REPLY_EMAIL !== "false"){
            $mail->AddReplyTo(SMTP_REPLY_EMAIL);
        }

        // Attachments TODO ATTACHMENT!
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        $mail->WordWrap = 80;
        $mail->IsHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $content;
        if (!$mail->Send()) {
            echo 'HIBA! Készítsen fotót a hibaüzenetről és küldje el nekünk!<br>';
            echo 'A felmerült hiba: ' . $mail->ErrorInfo;
            die();
        }
        return true;
    }
    
}