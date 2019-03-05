<?php

// FONCTIONNEMENT :

namespace App\GC\PdfBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Spipu\Html2Pdf\Html2Pdf;

class PDF extends Bundle
{
    /**
     * OBJECT MANAGER TYPE
     */
    protected $pdf;

    public function __construct() {
    }

    private function build_table($array){
        // start table
        $html = '
        <style>
          .tbl{
           width: 595pt;
           border: .5px solid #000;
          }
          td {
            border-left: solid .5px #000;
          }
          .nobreak {
              page-break-inside: avoid;
              page-break-after: avoid;
              page-break-before: avoid;
          }

          th {
            text-align: center;
          }
        </style>
        <page>
        <table class="tbl" CELLSPACING="0" cellpadding="2">
        ';
        // header row
        $html .= '<tr>';
        foreach($array[0] as $key=>$value){
                $html .= '<th width="80" style="font-size:9px;background: #E0E0E0;">' . htmlspecialchars($value) . '</th>';
            }
        $html .= '</tr>';

        // data rows
        $odd = 0;
        foreach( $array as $key=>$value){
            if($key != 0){
              $html .= '<tr>';
              foreach($value as $key2=>$value2){
                $html .= '<td width="80" height="100%" class="nobreak" style="height:100%;font-size:8px;';
                if($odd % 2 == 0){
                  $html.= 'background: #E0E0E0;';
                }
                // if(strlen($value2) > 20){
                //   $value2 = substr($value2,0,20).' '.substr($value2,20,20);
                // }
                $html.= '">' . htmlspecialchars($value2) . '</td>';
              }
              $html .= '</tr>';
            }
            $odd++;
        }

        // finish table and return it

        $html .= '</table></page>';
        return $html;
    }

    public function array_to_pdf_download($array, $filename = "export.pdf") {
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
        // PDF
        try {
            $content = $this->build_table($array);
            $html2pdf = new Html2Pdf('L', 'A4', 'fr', true, 'UTF-8', 3);
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($content);
            $html2pdf->output($filename, 'D');
            return 1;
        } catch (Html2PdfException $e) {
            $html2pdf->clean();
            $formatter = new ExceptionFormatter($e);
            return $formatter->getHtmlMessage();
        }
    }

}
