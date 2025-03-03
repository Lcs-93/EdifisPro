<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    // Route pour le dashboard de l'utilisateur
    #[Route('/user', name: 'user_dashboard')]
    public function index(): Response
    {
        // Vérifie que l'utilisateur a bien le rôle ROLE_USER
        if (!$this->isGranted('ROLE_USER')) {
            // Redirige si l'utilisateur n'est pas authentifié ou n'a pas le rôle ROLE_USER
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/index.html.twig');
    }
}
