<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="connexion")
     */
    public function connexion(Request $request)
    {
        // SUBMIT CONTACT
        $submitContact = $request->request->get('submitContact');
        if($submitContact != null){
          $arrayReturn["error"] = "Une erreur est survenue";
        }

        // SUBMIT PSEUDO
        $submitContact = $request->request->get('submitPseudo');
        if($submitContact != null){
          $arrayReturn["error"] = "Le jeu est en cours de construction";
        }

        $arrayReturn["controller_name"] = 'Commençons l\'aventure';

        return $this->render('main/connexion.html.twig', $arrayReturn);
    }

    /**
     * @Route("/index", name="index")
     */
    public function index(Request $request)
    {

      $arrayReturn["controller_name"] = 'Fil d\'actualité';
      return $this->render('main/index.html.twig', $arrayReturn);
    }
}
