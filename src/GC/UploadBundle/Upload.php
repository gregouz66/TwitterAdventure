<?php

// FONCTIONNEMENT :
// $upload = new Upload("taches", $file);
// $upload->preUploadFile();
// $pathFile = $upload->uploadFile();
// dump($pathFile); //VOUS AVEZ ICI LE CHEMIN DEPUIS PUBLIC/ASSETS/FICHIER

// NE PAS OUBLIER DE CHANGER LE CHEMIN POUR VOS FICHIERS EN RAPPORT AVEC VOTRE PROJET (function getTmpUploadRootDir())

namespace App\GC\UploadBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Upload extends Bundle
{
    /**
     * FILE TYPE
     */
    protected $file;

    /**
     * STRING TYPE
     */
    protected $image;

    /**
     * STRING TYPE
     */
    protected $dossier;

    public function __construct($dossier = null, $file = null) {
      if($file != null){
        $this->file = $file;
      }
      if($dossier != null){
        $this->dossier = $dossier;
      }
    }

      public function setFile($file): self
      {
          $this->file = $file;
          return $this;
      }

      public function setDossier($dossier): self
      {
          $this->dossier = $dossier;
          return $this;
      }

      // UPLOAD FILE

      public function getFullImagePath()
      {
          return null === $this->image ? null : $this->getUploadRootDir().$this->image;
      }

      public function getImagePath()
      {
        if($this->image != null AND $this->dossier != null){
          return $this->dossier."/".$this->image;
        }
      }

      protected function getUploadRootDir()
      {
          // the absolute directory path where uploaded documents should be saved
          if($this->dossier == null){
            $this->dossier = "undefined";
          }
          return $this->getTmpUploadRootDir().$this->dossier."/";
      }

      protected function getTmpUploadRootDir()
      {
          // the absolute directory path where uploaded documents should be saved
          return __DIR__ . '/../../../public/assets/fichiers/';
      }

      public function preUploadFile(){
        if (null !== $this->file) {
          $fullName = $this->file->getClientOriginalName();
          $duplicata = "";
          $i = 1;
          while(file_exists($this->getUploadRootDir().$duplicata.$fullName)){
            $duplicata = "(".$i.")";
            $i++;
          }
          $this->image = $duplicata.$fullName;
        }
      }

      public function uploadFile(){
        if (null === $this->file) {
          return;
        }
        if(!is_dir($this->getUploadRootDir())){
          mkdir($this->getUploadRootDir());
        }
        $this->file->move($this->getUploadRootDir(), $this->image);
        $response = $this->getImagePath();
        unset($this->image);
        unset($this->file);
        return $response;
      }

      // DELETE FILE
      public function delete_file($path){
        if(file_exists($this->getTmpUploadRootDir().$path)){
          unlink($this->getTmpUploadRootDir().$path);
        }
      }
}
