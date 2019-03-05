<?php
namespace App\GC\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CodeInsertHTML extends Bundle
{
  protected $string;

  public function __construct() {
  }

  public function generateCodeHTML($string){
    $this->string = $string;

    $i = true;

    while ($i === true) { // TANT QUE L'ONT TROUVE DU HTML A CONVERTIR
      $pos = strpos($string, '|%'); //CHERCHE SI IL Y A DU HTML A CONVERTIR
      if ($pos !== false) { //SI IL Y EN A ALORS ON COMMENCE LE CONVERTISSEUR

        $count = strlen($string);
        $pos2 = strpos($string, '%|');
        $posFinish = ($count - $pos2) - 2;

        $oldstring = substr($string, $pos, -$posFinish); //Récup la partie a convertir
        $oldstringFilter = str_replace('|%', '', $oldstring);
        $oldstringFilter = str_replace('%|', '', $oldstringFilter); //filtre en enlevant les caractères pour la trouver
        $newstring = htmlentities($oldstringFilter); //Convertit la parite du code
        $string = str_replace($oldstring, $newstring, $string); //REMPLACER LE NON CONVRTIT PAR LE CONVERTIT

      } else {
        $i = false;
      }
    }

    $this->string = $string;
    return $string;

  }
}
