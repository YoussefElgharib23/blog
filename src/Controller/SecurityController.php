<?php

namespace App\Controller;

use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser() AND $this->isGranted('ROLE_ADMIN')) {
             return $this->redirectToRoute('app_admin_index');
         }
         elseif ($this->getUser() AND $this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_client_index');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     * @param FlashyNotifier $flashyNotifier
     */
    public function logout(FlashyNotifier $flashyNotifier)
    {
        $flashyNotifier->success('You are logged out !');
    }
}
