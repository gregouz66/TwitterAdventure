<?php
namespace App\GC\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TokenConfirm extends Bundle
{
    protected $time,
        $ref,
        $alphabet;

    public function __construct() {
        $this->time = "";
        $this->ref = "ACTIVE_";
        $this->alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    }

    public function generateTokenConfirm() {
        $this->time = time();
        $arraytime = str_split($this->time);
        foreach ($arraytime as $key => $value) {
            if($key == 0 OR $key == 1 OR $key == 3 OR $key == 5){
                $this->ref .= $this->alphabet[$value];
            } else {
                $this->ref .= $value;
            }
        }
        return $this->ref;
    }
}