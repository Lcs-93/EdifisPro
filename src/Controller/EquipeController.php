<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Entity\EquipeUser;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/equipe')]
final class EquipeController extends AbstractController
{
    #[Route(name: 'app_equipe_index', methods: ['GET'])]
    public function index(EquipeRepository $equipeRepository): Response
    {
        return $this->render('equipe/index.html.twig', [
            'equipes' => $equipeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_equipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedUsers = $form->get('membres')->getData();
            $dateDebut = $form->get('dateDebut')->getData();
            $dateFin = $form->get('dateFin')->getData();

            foreach ($selectedUsers as $user) {
                // Vérifier si l'utilisateur est déjà affecté dans cette période
                $existingAssignments = $user->getEquipeUsers();
                foreach ($existingAssignments as $assignment) {
                    if (($dateDebut < $assignment->getDateFin()) && ($dateFin > $assignment->getDateDebut())) {
                        $this->addFlash('danger', "L'utilisateur {$user->getNom()} est déjà affecté à une équipe entre {$assignment->getDateDebut()->format('d/m/Y')} et {$assignment->getDateFin()->format('d/m/Y')}.");
                        return $this->redirectToRoute('app_equipe_new');
                    }
                }

                $equipeUser = new EquipeUser();
                $equipeUser->setUtilisateur($user);
                $equipeUser->setEquipe($equipe);
                $equipeUser->setDateDebut($dateDebut);
                $equipeUser->setDateFin($dateFin);

                $entityManager->persist($equipeUser);
            }

            $entityManager->persist($equipe);
            $entityManager->flush();

            return $this->redirectToRoute('app_equipe_index');
        }

        return $this->render('equipe/new.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_show', methods: ['GET'])]
    public function show(Equipe $equipe): Response
    {
        return $this->render('equipe/show.html.twig', [
            'equipe' => $equipe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipe $equipe, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $allUsers = $userRepository->findAll();
        $currentMembers = [];

        foreach ($equipe->getEquipeUsers() as $equipeUser) {
            $currentMembers[] = $equipeUser->getUtilisateur();
        }

        $form = $this->createForm(EquipeType::class, $equipe);
        $form->get('membres')->setData($currentMembers);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedUsers = $form->get('membres')->getData();
            $dateDebut = $form->get('dateDebut')->getData();
            $dateFin = $form->get('dateFin')->getData();

            // Vérifier les conflits avant modification
            foreach ($selectedUsers as $user) {
                foreach ($user->getEquipeUsers() as $assignment) {
                    if ($assignment->getEquipe() !== $equipe) {
                        if (($dateDebut < $assignment->getDateFin()) && ($dateFin > $assignment->getDateDebut())) {
                            $this->addFlash('danger', "L'utilisateur {$user->getNom()} est déjà affecté à une équipe entre {$assignment->getDateDebut()->format('d/m/Y')} et {$assignment->getDateFin()->format('d/m/Y')}. Modification impossible.");
                            return $this->redirectToRoute('app_equipe_edit', ['id' => $equipe->getId()]);
                        }
                    }
                }
            }

            // Supprimer les membres désélectionnés
            foreach ($equipe->getEquipeUsers() as $equipeUser) {
                if (!in_array($equipeUser->getUtilisateur(), $selectedUsers)) {
                    $entityManager->remove($equipeUser);
                }
            }

            // Ajouter les nouveaux membres
            foreach ($selectedUsers as $user) {
                if (!in_array($user, $currentMembers)) {
                    $equipeUser = new EquipeUser();
                    $equipeUser->setUtilisateur($user);
                    $equipeUser->setEquipe($equipe);
                    $equipeUser->setDateDebut($dateDebut);
                    $equipeUser->setDateFin($dateFin);
                    $entityManager->persist($equipeUser);
                }
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_equipe_index');
        }

        return $this->render('equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView(),
            'allUsers' => $allUsers,
            'currentMembers' => $currentMembers
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_delete', methods: ['POST'])]
    public function delete(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $equipe->getId(), $request->request->get('_token'))) {
            foreach ($equipe->getEquipeUsers() as $equipeUser) {
                $entityManager->remove($equipeUser);
            }
            $entityManager->remove($equipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_equipe_index');
    }
}
