<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface; // ✅ Correct
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // ✅ Correct
use Symfony\Component\HttpFoundation\Request; // ✅ Correct
use App\Entity\User; // ✅ Correct
use App\Form\UserEditFormType; // ✅ Correct


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

    #[Route('/user/edit-modal', name: 'user_edit_modal')]
    public function editModal(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new Response("Utilisateur non connecté", Response::HTTP_UNAUTHORIZED);
        }

        // Création du formulaire
        $form = $this->createForm(UserEditFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return new Response("Profil mis à jour avec succès", Response::HTTP_OK);
        }

        return $this->render('user/edit_modal.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
