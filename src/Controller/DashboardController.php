<?php

namespace App\Controller;

use App\Entity\Chantier;
use App\Repository\ChantierRepository;
use App\Repository\AffectationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard', methods: ['GET'])]
    public function index(ChantierRepository $chantierRepository, AffectationRepository $affectationRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour accéder au tableau de bord.");
        }

        // 🛑 Si l'utilisateur est un ADMIN, il voit tous les chantiers
        if ($this->isGranted('ROLE_ADMIN')) {
            $chantiers = $chantierRepository->findAll();
        } else {
            // 🔹 Si c'est un utilisateur normal, on récupère seulement ses chantiers
            $chantiers = $chantierRepository->createQueryBuilder('c')
            ->distinct()
            ->join('c.affectations', 'a')
            ->join('a.equipe', 'e')
            ->join('e.equipeUsers', 'eu')
            ->where('eu.utilisateur = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
        
        }

        return $this->render('dashboard/index.html.twig', [
            'chantiers' => $chantiers,
            'user' => $user,
            'is_admin' => $this->isGranted('ROLE_ADMIN'),
        ]);
    }
}
