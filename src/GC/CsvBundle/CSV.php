<?php

// FONCTIONNEMENT :

namespace App\GC\CsvBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpFoundation\JsonResponse;

class CSV extends Bundle
{
    /**
     * OBJECT MANAGER TYPE
     */
    protected $csv;

    public function __construct() {
    }

    public function array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
        // GET KEYS of FIRST COLUMN
        $i = 0;
        foreach ($array as $object) {
          if($i == 0){
            foreach ($object as $key => $value) {
              $head_array[] = $key;
            }
          }
          $i++;
        }
        array_unshift($array, $head_array);
        // open raw memory as file so no temp files needed, you might run out of memory though
        $f = fopen('php://memory', 'w');
        // loop over the input array
        foreach ($array as $line) {
            // generate csv lines from the inner arrays
            fputcsv($f, $line, $delimiter);
        }
        // reset the file pointer to the start of the file
        fseek($f, 0);
        // tell the browser it's going to be a csv file
        header('Content-Description: Export CSV');
        header('Content-Type: application/csv; charset=UTF-8');
        // tell the browser we want to save it instead of displaying it
        header('Content-Disposition: attachment; filename="'.$filename.'";');
        // ENCODING
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        // header('Content-Encoding: UTF-8');

        // make php send the generated csv lines to the browser
        fpassthru($f);
    }

}
