<?php
namespace App\GC\CommandeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class UniqRef extends Bundle
{
    protected $time,
        $ref,
        $alphabet;

    public function __construct() {
        $this->time = "";
        $this->ref = "COM_";
        $this->alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    }

    public function generateRef() {
        $this->time = time();
        $arraytime = str_split($this->time);
        foreach ($arraytime as $key => $value) {
            if($key == 0 OR $key == 1 OR $key == 2){
                $this->ref .= $this->alphabet[$value];
            } else {
                $this->ref .= $value;
            }
        }
        return $this->ref;
    }
}