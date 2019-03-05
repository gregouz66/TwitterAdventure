<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\GC\UserBundle\UserValidate;

// SESSION
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/", name="connexion")
     */
    public function connexion(Request $request, SessionInterface $session)
    {
        // SUBMIT CONTACT
        $submitContact = $request->request->get('submitContact');
        if($submitContact != null){
          $this->addFlash("danger", "Une erreur est survenue");
        }

        // SUBMIT PSEUDO
        $submitContact = $request->request->get('submitPseudo');
        $pseudo = $request->request->get('pseudo');
        if($submitContact != null){
          $userValidate = new UserValidate();
          $validePseudo = $userValidate->validatePseudo($pseudo);
          if($validePseudo["statut"] == 1){
            // dump($validePseudo);
            $session->set('NAME_PLAYER', $validePseudo["pseudo"]);
          } else {
            $this->addFlash("danger", $validePseudo["message"]);
          }
        }

        // IF GO TO PLAY
        if($session->get('NAME_PLAYER')){
          return $this->redirectToRoute("index");
        }

        $arrayReturn["controller_name"] = 'Commençons l\'aventure';

        return $this->render('security/connexion.html.twig', $arrayReturn);
    }

    /**
     * La route pour se deconnecter.
     *
     * @Route("/logout", name="logout")
     */
    public function logout(SessionInterface $session)
    {
        $session->clear();
        return $this->redirectToRoute('connexion');
    }
}
