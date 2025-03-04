<?php

namespace App\Controller;

use App\Entity\EquipeUser;
use App\Form\EquipeUserType;
use App\Repository\EquipeUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/equipe/user')]
final class EquipeUserController extends AbstractController
{
    // Liste des utilisateurs dans l'équipe
    #[Route('', name: 'app_equipe_user_index', methods: ['GET'])]
    public function index(EquipeUserRepository $equipeUserRepository): Response
    {
        $equipeUsers = $equipeUserRepository->findAll();

        // Optionnel : Vous pouvez ajouter un message flash pour informer l'utilisateur d'une action réussie
        // $this->addFlash('notice', 'Page chargée avec succès');

        return $this->render('equipe_user/index.html.twig', [
            'equipe_users' => $equipeUsers,
        ]);
    }

    // Création d'un utilisateur dans l'équipe
    #[Route('/new', name: 'app_equipe_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipeUser = new EquipeUser();
        $form = $this->createForm(EquipeUserType::class, $equipeUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipeUser);
            $entityManager->flush();

            // Redirige vers la liste après l'ajout
            $this->addFlash('success', 'Utilisateur ajouté avec succès');

            return $this->redirectToRoute('app_equipe_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipe_user/new.html.twig', [
            'equipe_user' => $equipeUser,
            'form' => $form,
        ]);
    }

    // Afficher les détails d'un utilisateur dans l'équipe
    #[Route('/{id}', name: 'app_equipe_user_show', methods: ['GET'])]
    public function show(EquipeUser $equipeUser): Response
    {
        return $this->render('equipe_user/show.html.twig', [
            'equipe_user' => $equipeUser,
        ]);
    }

    // Modifier un utilisateur dans l'équipe
    #[Route('/{id}/edit', name: 'app_equipe_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EquipeUser $equipeUser, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipeUserType::class, $equipeUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire est validé, on enregistre les modifications
            $entityManager->flush();

            // Message flash pour informer de la réussite de l'édition
            $this->addFlash('success', 'Utilisateur modifié avec succès');

            return $this->redirectToRoute('app_equipe_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipe_user/edit.html.twig', [
            'equipe_user' => $equipeUser,
            'form' => $form,
        ]);
    }

    // Supprimer un utilisateur de l'équipe
    #[Route('/{id}', name: 'app_equipe_user_delete', methods: ['POST'])]
    public function delete(Request $request, EquipeUser $equipeUser, EntityManagerInterface $entityManager): Response
    {
        // Vérification du token CSRF pour éviter les attaques CSRF
        if ($this->isCsrfTokenValid('delete' . $equipeUser->getId(), $request->request->get('_token'))) {
            $entityManager->remove($equipeUser);
            $entityManager->flush();

            // Message flash pour notifier que l'utilisateur a été supprimé
            $this->addFlash('success', 'Utilisateur supprimé avec succès');
        }

        return $this->redirectToRoute('app_equipe_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
