<?php
namespace App\GC\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class UserValidate extends Bundle
{
    protected $pseudo,
        $password;

    public function __construct() {

    }

    public function validatePseudo($pseudo) {
        // UNSET FOR REUSABLE
        $response["message"] = null;
        // VALIDATIONS
        if(empty($pseudo)){
          $response["message"] = "Le pseudo ne doit pas être vide.";
        } elseif(strlen($pseudo) < 2 OR strlen($pseudo) > 20){
          $response["message"] = "Le pseudo doit contenir entre 2 et 20 caractères.";
        }
        // FILTERS
        if(empty($response["message"])){
          // FILTERS
          // Remplace les caractères accentués par leurs équivalents sans accents.
          $pseudo = htmlentities($pseudo, ENT_NOQUOTES, 'utf-8');
          $pseudo = preg_replace('#&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring);#', '\1', $pseudo);
          $pseudo = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $pseudo);
          $pseudo = preg_replace('#&[^;]+;#', '', $pseudo);
          //Supprime tous les espaces
          $pseudo = str_replace(' ', '_', $pseudo);
          //return 1 & pseudo
          $response["statut"] = 1;
          $response["pseudo"] = $pseudo;
        } else {
          $response["statut"] = 0;
        }
        // RETURN
        return $response;
    }
}
