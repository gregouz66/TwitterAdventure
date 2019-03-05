<?php
/*
        ---------- RollingCurlX 3.0.2 -----------
        an easy to use curl_multi wrapper for php

            Copyright (c) 2015-2017 Marcus Leath
                    License: MIT
        https://github.com/marcushat/RollingCurlX
*/
namespace App\GC\RollingCurlBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

Class RollingCurlX extends Bundle {

    private $url = NULL;
    private $httpResponse = NULL;

    function __construct($url, $timeout = 10) {
      $this->url = $url;
      // Initialisation d'une session cURL
      $ch = curl_init($url);
      // Forcer l'utilisation d'une nouvelle connexion (pas de cache)
      curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
      // Définition du timeout de la requête (en secondes)
      curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
      // Si l'URL est en HTTPS
      if (preg_match('`^https://`i', $url))
      {
      // Ne pas vérifier la validité du certificat SSL
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      }
      // Suivre les redirections [facultatif]
      // www.oseox.fr redirige par exemple automatiquement vers oseox.fr
      // Le code de retour serait ici 301 si l'on ne suivait pas les redirections
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      // Récupération du contenu retourné par la requête
      // sous forme de chaîne de caractères via curl_exec()
      // (directement affiché au navigateur client sinon)
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // Ne pas récupérer le contenu de la page requêtée
      curl_setopt($ch, CURLOPT_NOBODY, true);
      // Execution de la requête
      curl_exec($ch);
      // Récupération du code HTTP retourné par la requête
      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      // Fermeture de la session cURL
      curl_close($ch);
      $this->httpResponse = $http_code;
    }

    public function getHttpResponse(){
      return $this->httpResponse;
    }

}
