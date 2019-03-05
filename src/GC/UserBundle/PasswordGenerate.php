<?php
namespace App\GC\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PasswordGenerate extends Bundle
{
    protected $password,
        $alphabet;

    public function __construct() {
        // $this->password = "";
        $this->alphabet = array('A','$','!','d','E','?','g','H','£','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        $passwordGenerate ="";
        for ($k = 0 ; $k < 15; $k++){
          $passwordGenerate .= rand(0, 9);
        }
        $arrayPasswordGenerate = str_split($passwordGenerate);
        foreach ($arrayPasswordGenerate as $key => $value) {
            if($key == 0 OR $key == 1 OR $key == 3 OR $key == 5 OR $key == 7   OR $key == 8 OR $key == 9 OR $key == 11 OR $key == 12 OR $key == 13 OR $key == 15){
                $this->password .= $this->alphabet[$value];
            } else {
                $this->password .= $value;
            }
        }
        // $this->password .= "";
    }

    public function getPassword(){
      return $this->password;
    }

    public function sendRecovery($email, $password)
    {
      //SEND MAIL TO USER
      try {
        //SEND MAIL()
        $to  = $email; // notez la virgule
        // Sujet
        $subject = 'Réinitialisation de mot de passe - AWBoard Impulsion';
        // message
        $message = "
        Vous avez réinitialisé votre mot de passe sur <a href='http://services.impulsion.fr'>services.impulsion.fr</a><br><br>
        Votre identifiant : <strong>$email</strong><br>
        Votre nouveau mot de passe : <strong>$password</strong><br><hr>
        L'équipe Impulsion.
        ";

        // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        // En-têtes additionnels
        // $headers[] = 'To: Grégory Cascales <gregory@cascales.fr>';
        $headers[] = 'From: Impulsion <contact@impulsion.fr>';
        // Envoi
        mail($to, $subject, $message, implode("\r\n", $headers));
        return "1";
      } catch (\Exception $e) {
        return $e->getMessage();
      }
    }
}
