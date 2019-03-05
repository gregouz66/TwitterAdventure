<?php
namespace App\GC\AnalyticsAPIBundle;

// METRICS DISPO :
// https://developers.google.com/analytics/devguides/reporting/core/dimsmets

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Google_Client;
use Google_Service_Analytics;
use Symfony\Component\HttpFoundation\JsonResponse;

class Analytics extends Bundle
{
    protected $analytics,
        $profileId,
        $lastResults,
        $lastResultsPHP,
        $listMois = [
          "01"=> "Janvier",
          "02"=> "Février",
          "03"=> "Mars",
          "04"=> "Avril",
          "05"=> "Mai",
          "06"=> "Juin",
          "07"=> "Juillet",
          "08"=> "Août",
          "09"=> "Septembre",
          "10"=> "Octobre",
          "11"=> "Novembre",
          "12"=> "Décembre"
        ];

    public function __construct($KEY_FILE_LOCATION) {
      $this->analytics = $this->initializeAnalytics($KEY_FILE_LOCATION); // Initialisera l'API
      $this->profileId = $this->getFirstProfileId(); // Récupère le profil Google Analytics
    }

    public function getLastResults() {
      return $this->lastResults;
    }

    public function getLastResultsPHP() {
      return $this->lastResultsPHP;
    }

    // Fonction d'initialisation et d'authentification
    private function initializeAnalytics($KEY_FILE_LOCATION) {
      // Crée et configure le client
      $client = new Google_Client();
      $client->setApplicationName("Hello Analytics Reporting");
      $client->setAuthConfig($KEY_FILE_LOCATION);
      $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
      $this->analytics = new Google_Service_Analytics($client);
      return $this->analytics;
    }

    // Récupère le profil Google Analytics
    private function getFirstProfileId() {
      // Récupère la liste des comptes
      $accounts = $this->analytics->management_accounts->listManagementAccounts();

      if (count($accounts->getItems()) > 0) {
        $items = $accounts->getItems();
        $firstAccountId = $items[0]->getId();

        // Récupère la liste des propriétés
        $properties = $this->analytics->management_webproperties
            ->listManagementWebproperties($firstAccountId);

        if (count($properties->getItems()) > 0) {
          $items = $properties->getItems();
          $firstPropertyId = $items[0]->getId();

          // Récupère la liste des vues
          $this->profiles = $this->analytics->management_profiles
              ->listManagementProfiles($firstAccountId, $firstPropertyId);

          if (count($this->profiles->getItems()) > 0) {
            $items = $this->profiles->getItems();

            // Retourne l'ID de la première vue
            return $items[0]->getId();

          } else {
            throw new Exception('No views (profiles) found for this user.');
          }
        } else {
          throw new Exception('No properties found for this user.');
        }
      } else {
        throw new Exception('No accounts found for this user.');
      }
    }

    // Fonction qui récupère les informations nécessaires pour le graphique depuis GoogleAnalytics
    private function getChartResults($date, $metric) {
      // DATE
      if(is_string($date)){ // SI LE RESULTAT EST EN STRING
        if(substr($date, -7) == "daysAgo"){ // IF DEFINIT UN NOMBRE DE JOURS
          $beginDate = $date;
        } else if(substr($date, -9) == "monthsAgo") { // IF DEFINIT UN NOMBRE DE MOIS
          $countMonths = substr($date, 0, -9);
          $beginDate = date("Y-m-d", strtotime("-".$countMonths." month", time()));
        } else {
          $beginDate = "3daysAgo";
        }
        $endDate = "today"; //Le endDate ne changera jamais car ce sera la date d'arrivé (today)
      } else { // SI LE RESULTAT EST UN TABLEAU DE DATE (DATE A TO DATE B)
        $beginDate = $date[0];
        $endDate = $date[1];
      }
      // GET RESULT
      $this->lastResults = $this->analytics->data_ga->get(
        'ga:' . $this->profileId,
        $beginDate,
        $endDate,
        $metric,
        array(
          'dimensions'=>'ga:pageTitle'
          // ga:city
        )
      );
    }

    // Cette fonction crée le tableau qui pourra être lu par le javascript (MorrisCharts)
    private function buildChartArray($filter = null) {
      $results = $this->lastResults;
      if (count($results->getRows()) > 0) {
        $rows = $results->getRows(); // On compte les lignes
        $array=[]; // Initialisation du tableau avec les nomn des "colonnes"
        foreach($rows as $date){ // Parcours des dates
          $datejour = substr($date[0],0,4)."-".substr($date[0],-4,2)."-".substr($date[0],-2,2); // On formatte la date (pour être joli à l'affichage)
          array_push($array,[$datejour,(int)$date[1],(int)$date[2],(int)$date[3]]); // On ajoute la date et les données du jour au tableau
        }
        //IF FILTER BYMONTH, ON REGROUPE TOUT POUR PAR MOIS
        if($filter == "byMonth"){
          $lastmonth = null;
          $newarray=[];
          $totalCount = array("PagesVues" => 0, "Visiteurs" => 0, "Visites" => 0);
          foreach ($array as $key => $value) {
            if($key != 0){ //ne pas prendre la premiere colonne avec le nom des valeurs
              $thismonth = date("m",strtotime($value[0]));
              if($lastmonth == null){ //Si c'est la premiere opération on prend son mois
                $lastmonth = $thismonth;
              }
              if($thismonth == $lastmonth){ //Si c'est le même mois on additionne
                $totalCount["PagesVues"] = $totalCount["PagesVues"] + $value[1];
                $totalCount["Visiteurs"] = $totalCount["Visiteurs"] + $value[2];
                $totalCount["Visites"] = $totalCount["Visites"] + $value[3];
              } else { //Sinon on passe a un autre mois
                array_push($newarray,[$lastmonth,(int)$totalCount["PagesVues"],(int)$totalCount["Visiteurs"],(int)$totalCount["Visites"]]);
                $totalCount = array("PagesVues" => 0, "Visiteurs" => 0, "Visites" => 0);
                $lastmonth = $thismonth;
                $totalCount["PagesVues"] = $totalCount["PagesVues"] + $value[1];
                $totalCount["Visiteurs"] = $totalCount["Visiteurs"] + $value[2];
                $totalCount["Visites"] = $totalCount["Visites"] + $value[3];
              }
            }
          }
          array_push($newarray,[$lastmonth,(int)$totalCount["PagesVues"],(int)$totalCount["Visiteurs"],(int)$totalCount["Visites"]]); //Push dernier mois non-finit
          $this->lastResultsPHP = $newarray; //getStatArrayPHP
          // On encode le tout en json
          $js_array=json_encode($newarray);
        } else {
          $this->lastResultsPHP = $array; //getStatArrayPHP
          // On encode le tout en json
          $js_array=json_encode($array);
        }
        return $js_array; // On le retourne
      } else {
        return "Pas de résultat.\n";
      }
    }

    //Cette fonction crée le tableau qui pourra être lu par le javascript (GoogleCharts)
    private function buildChartArrayGoogle($filter = null) {
      $results = $this->lastResults;
      if (count($results->getRows()) > 0) {
        $rows = $results->getRows(); // On compte les lignes
        $array=[["Date","Pages Vues","Visiteurs","Visites"]]; // Initialisation du tableau avec les noms des "colonnes"
        foreach($rows as $date){ // Parcours des dates
          $datejour = substr($date[0],-2,2)."-".substr($date[0],-4,2)."-".substr($date[0],0,4); // On formatte la date (pour être joli à l'affichage)
          array_push($array,[$datejour,(int)$date[1],(int)$date[2],(int)$date[3]]); // On ajoute la date et les données du jour au tableau
        }
        //IF FILTER BYMONTH, ON REGROUPE TOUT POUR PAR MOIS
        if($filter == "byMonth"){
          $lastmonth = null;
          $newarray=[["Mois","Pages Vues","Visiteurs","Visites"]];
          $totalCount = array("PagesVues" => 0, "Visiteurs" => 0, "Visites" => 0);
          foreach ($array as $key => $value) {
            if($key != 0){ //ne pas prendre la premiere colonne avec le nom des valeurs
              $thismonth = date("m",strtotime($value[0]));
              if($lastmonth == null){ //Si c'est la premiere opération on prend son mois
                $lastmonth = $thismonth;
              }
              if($thismonth == $lastmonth){ //Si c'est le même mois on additionne
                $totalCount["PagesVues"] = $totalCount["PagesVues"] + $value[1];
                $totalCount["Visiteurs"] = $totalCount["Visiteurs"] + $value[2];
                $totalCount["Visites"] = $totalCount["Visites"] + $value[3];
              } else { //Sinon on passe a un autre mois
                array_push($newarray,[$lastmonth,(int)$totalCount["PagesVues"],(int)$totalCount["Visiteurs"],(int)$totalCount["Visites"]]);
                $totalCount = array("PagesVues" => 0, "Visiteurs" => 0, "Visites" => 0);
                $lastmonth = $thismonth;
                $totalCount["PagesVues"] = $totalCount["PagesVues"] + $value[1];
                $totalCount["Visiteurs"] = $totalCount["Visiteurs"] + $value[2];
                $totalCount["Visites"] = $totalCount["Visites"] + $value[3];
              }
            }
          }
          array_push($newarray,[$lastmonth,(int)$totalCount["PagesVues"],(int)$totalCount["Visiteurs"],(int)$totalCount["Visites"]]); //Push dernier mois non-finit
          $this->lastResultsPHP = $newarray; //getStatArrayPHP
          // On encode le tout en json
          $js_array=json_encode($newarray);
        } else {
          $this->lastResultsPHP = $array; //getStatArrayPHP
          // On encode le tout en json
          $js_array=json_encode($array);
        }
        return $js_array; // On le retourne
      } else {
        return "Pas de résultat.\n";
      }
    }

    //Fonction pour acceder aux résultats (MorrisCharts)
    public function morrisChart($date, $listMetric, $filter = null) {
      $this->getChartResults($date,$listMetric);
      $resultArray = $this->buildChartArray($filter);
      return $resultArray;
    }

    //Fonction pour acceder aux résultats (GoogleChart)
    // UTILISER CELUI LA POUR DES STATS EN TABLEAU
    public function googleChart($date, $listMetric, $filter = null) {
      $this->getChartResults($date,$listMetric);
      $resultArray = $this->buildChartArrayGoogle($filter);
      return $resultArray;
    }

    public function convertJson($result) {
      $resultJson = new JsonResponse($result);
      return $resultJson->getContent();
    }

    public function getStats($date, $listMetric, $dimension, $filter = null, $max = null) {
        // DATE
        if(is_string($date)){ // SI LE RESULTAT EST EN STRING
          if(substr($date, -7) == "daysAgo"){ // IF DEFINIT UN NOMBRE DE JOURS
            $beginDate = $date;
          } else if(substr($date, -9) == "monthsAgo") { // IF DEFINIT UN NOMBRE DE MOIS
            $countMonths = substr($date, 0, -9);
            $beginDate = date("Y-m-d", strtotime("-".$countMonths." month", time()));
          } else {
            $beginDate = "3daysAgo";
          }
          $endDate = "today"; //Le endDate ne changera jamais car ce sera la date d'arrivé (today)
        } else { // SI LE RESULTAT EST UN TABLEAU DE DATE (DATE A TO DATE B)
          $beginDate = $date[0];
          $endDate = $date[1];
        }
        // GET RESULT
        $options = array(
          'dimensions' => $dimension["value"],
          'max-results' => $max,
          // ga:pageTitle
          // ga:city
        );
        if(!empty($filter)){
          $options["sort"] = $filter;
        }
        $lastResult = $this->analytics->data_ga->get(
          'ga:' . $this->profileId,
          $beginDate,
          $endDate,
          $listMetric["value"],
          $options
        );
        $lastResultArray = $lastResult->getRows();
        //if is DATE dimension = change format date
        if($dimension["value"] == "ga:date"){
          $i=0;
          foreach ($lastResultArray as $key => $value) {
            $lastResultArray[$i][0] = substr($value[0], 6, 2)."/".substr($value[0], 4, 2)."/".substr($value[0], 2,2);
            $i++;
          }
        } elseif ($dimension["value"] == "ga:yearMonth"){
          $i=0;
          foreach ($lastResultArray as $key => $value) {
            // GET MOIS LETTERS FR
            $month = substr($value[0], -2);
            $year = substr($value[0], 0, -2);
            $lastResultArray[$i][0] = $this->listMois[$month];
            $lastResultArray[$i][0] = $lastResultArray[$i][0] . ' ' . $year;
            // GET ANNEE
            // if($value[0]+date('m') > 12){
            //   $Year = date('Y') - 1;
            // } else {
            //   $Year = date('Y');
            // }
            // $lastResultArray[$i][0] = $lastResultArray[$i][0] . ' '.$Year;
              // ENCREMENT
            $i++;
          }
        }
        //ADD Head property
        $lastResultArray["head"] = [$dimension["label"]];
        $MetricArray = explode(",", $listMetric["label"]);
        foreach ($MetricArray as $value) {
          array_push($lastResultArray["head"], $value);
        }

        $this->lastResults = $lastResultArray;
        return $this->lastResults;
    }

    public function getDateWithString($dateString, $what = null){

      // PREVIOUS DAY
      $date = new \DateTime(date('Y-m-d', strtotime(' -1 day')));
      $previousDay = $date->format("Y-m-d");
      // PREVIOUS 7DAYS
      $date = new \DateTime(date('Y-m-d', strtotime(' -7 day')));
      $previous7Day = $date->format("Y-m-d");
      // FIRST DAY OF LAST MONTH
      $date = new \DateTime("first day of previous month");
      $lastMonth["firstDay"] = $date->format("Y-m-d");
      // LAST DAY OF LAST MONTH
      $date = new \DateTime("last day of previous month");
      $lastMonth["lastDay"] = $date->format("Y-m-d");
      // FIRST DAY OF PREV MONTH (x3)
      $date = new \DateTime("first day of previous month");
      $date->modify("first day of previous month");
      $date->modify("first day of previous month");
      $treeMonth["firstDay"] = $date->format("Y-m-d");
      // FIRST DAY OF PREV PREV MONTH (x6)
      $date = new \DateTime("first day of previous month");
      $date->modify("first day of previous month");
      $date->modify("first day of previous month");
      $date->modify("first day of previous month");
      $date->modify("first day of previous month");
      $date->modify("first day of previous month");
      $sixMonth["firstDay"] = $date->format("Y-m-d");
      // FIRST DAY PREV YEAR
      $date = new \DateTime("first day of previous year");
      $date->modify("first day of January");
      $lastYear["firstDay"] = $date->format("Y-m-d");

      $libraryDates = [
        "previousDay" => $previousDay,
        "previous7Day" => $previous7Day,
        "lastMonth" => $lastMonth,
        "treeMonth" => $treeMonth,
        "sixMonth" => $sixMonth,
        "lastYear" => $lastYear,
      ];

      if(array_key_exists($dateString, $libraryDates)){
        if(is_array($libraryDates[$dateString])){
          if(array_key_exists($what, $libraryDates[$dateString])){
            return $libraryDates[$dateString][$what];
          } else {
            return "you have to write in second parameter 'firstDay' or 'lastDay'";
          }
        } else {
          return $libraryDates[$dateString];
        }
      }
      return "'$dateString' does not exist in the current context";
    }
}
