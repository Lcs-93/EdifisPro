<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    // Route pour le dashboard de l'administrateur
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        // Vérifie que l'utilisateur a bien le rôle ROLE_ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirige si l'utilisateur n'est pas un administrateur
            return $this->redirectToRoute('user_dashboard');
        }

        return $this->render('admin/index.html.twig');
    }
}
