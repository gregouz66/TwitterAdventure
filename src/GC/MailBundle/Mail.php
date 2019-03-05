<?php

// FONCTIONNEMENT :

namespace App\GC\MailBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpFoundation\JsonResponse;
use PHPMailer\PHPMailer\PHPMailer;

class Mail extends Bundle
{
    /**
     * OBJECT PHPMailer TYPE
     */
    protected $mail;

    public function __construct() {
      // PHPMailer
      $mail = new PHPMailer();
      $mail->IsSMTP();
      $mail->SMTPAuth = true; // enable SMTP authentication
      // $mail->SMTPSecure = "ssl"; // sets the prefix to the servier
      $mail->SMTPSecure = "tls";
      // $mail->Host = "SSL0.OVH.NET"; // SMTP server
      $mail->Host = "pro1.mail.ovh.net";
      // $mail->Port = 465; // set the SMTP port
      $mail->Port = 587;
      $mail->Username = "contact@impulsion.fr"; // Email
      $mail->Password = "GxyFJA*&pbNQM^lx4Zrwb3Ca850"; //Password
      $mail->CharSet = 'UTF-8';
      $mail->Encoding = 'base64';
      $mail->IsHTML(true);
      $this->mail = $mail;
    }

    public function sendEmail($subject, $content, $to){
      $this->mail->From = "contact@impulsion.fr";
      $this->mail->FromName = "Agence Impulsion";
      $this->mail->Subject = $subject;
      $this->mail->Body = $content;
      // CC if have multiple $to
      if(is_array($to)){
        $this->mail->AddAddress($to[0], "");
        foreach ($to as $email) {
          $this->mail->AddBCC($email, "");
        }
      } else {
        $this->mail->AddAddress($to, "");
      }
      // RETURN
      if($this->mail->Send() === true){
        return true;
      } else {
        return "error : ".$this->mail->ErrorInfo;
      }
    }
  }
