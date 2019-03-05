<?php
namespace App\GC\CryptBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Crypt extends Bundle
{
  // protected $string,
  //     $action;
  //
  // public function __construct() {
  //     $this->string = null;
  //     $this->action = null;
  // }

  function dec_enc($action, $string) {
      $output = false;

      $encrypt_method = "AES-256-CBC";
      $secret_key = 'impcoding$key17';
      $secret_iv = 'imp!codingiv7';

      // hash
      $key = hash('sha256', $secret_key);

      // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
      $iv = substr(hash('sha256', $secret_iv), 0, 16);

      if( $action == 'encrypt' ) {
        //Encrypt debut de chaine
        $i = 0;
        while($i < 4){
          $porf = rand(1,2);
          if($porf == 1){ //chiffre
            $chfalea = rand(0,9);
            $string = $chfalea.$string;
          } else { //lettre
            $alphabet="abcdefghijklmnopqrstuvwxyz";
            $lettrealea=$alphabet[rand(0,25)];
            $string = $lettrealea.$string;
          }
          $i++;
        }
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
      }
      else if( $action == 'decrypt' ){
          $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
      }

      return $output;
  }

  function encMax($string){ //INDECRYPTABLE
    $string = base64_encode(hash('sha512',$string, true));
    return $string;
  }

}
