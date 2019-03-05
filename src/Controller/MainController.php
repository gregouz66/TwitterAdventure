<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;

// SESSION
use Symfony\Component\HttpFoundation\Session\SessionInterface;

// MOBILE DETECT
use Detection\MobileDetect as Mobile_Detect;

class MainController extends AbstractController
{
    /**
     * @Route("/index", name="index")
     */
    public function index(Request $request, SessionInterface $session)
    {
      if(!$session->get('NAME_PLAYER')){
        return $this->redirectToRoute("connexion");
      }

      // IF IS MOBILE OR TABLET
      $detect = new Mobile_Detect;
      if($detect->isMobile() OR $detect->isTablet()){
        $this->addFlash("danger", "Peeper n'est pas encore adapté aux mobiles et tablettes, désolé !");
        return $this->redirectToRoute("logout");
      }

      $arrayReturn["controller_name"] = 'Fil d\'actualité';
      return $this->render('index/index.html.twig', $arrayReturn);
    }
}
