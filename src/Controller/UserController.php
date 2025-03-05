<?php

namespace App\Controller;

use App\Entity\CompetenceUser;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\UserEditFormType; // ✅ Correct

#[Route('/user')]
final class UserController extends AbstractController
{
    // Route pour le dashboard de l'utilisateur
    #[Route('', name: 'user_dashboard')]
    public function index(): Response
    {
        // Vérifie que l'utilisateur a bien le rôle ROLE_USER
        if (!$this->isGranted('ROLE_USER')) {
            // Redirige si l'utilisateur n'est pas authentifié ou n'a pas le rôle ROLE_USER
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/index.html.twig');
    }

    #[Route('/user_list', name: 'app_user_list', methods: ['GET'])]
    public function list(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository): Response
{
    $user = new User();

    // Vérifier si le formulaire a été soumis avant de tenter d'accéder aux données
    if ($request->isMethod('POST')) {
        $email = $request->get('user')['email'] ?? null; // Récupérer l'email soumis dans le formulaire, avec un fallback null si non défini
        if ($email) {
            $existingUser = $userRepository->findOneBy(['email' => $email]);

            if ($existingUser) {
                // Si un utilisateur avec le même email existe, afficher un message d'erreur
                $this->addFlash('danger', 'Cet email est déjà utilisé par un autre utilisateur.');
                return $this->render('app_user_list', [
                    'form' => $this->createForm(UserType::class, $user)->createView(),
                ]);
            }
        }
    }

    // Générer un mot de passe aléatoire
    $randomPassword = bin2hex(random_bytes(8)); // Génère un mot de passe aléatoire de 8 caractères
    $user->setPassword($passwordHasher->hashPassword($user, $randomPassword));

    // 🔹 Créer le formulaire en pré-remplissant le champ plainPassword
    $form = $this->createForm(UserType::class, $user, [
        'is_edit' => false,
        'generated_password' => $randomPassword, // On passe le MDP généré à UserType
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $competences = $form->get('competences')->getData();

        $newPassword = $form->get('plainPassword')->getData();
        if ($newPassword) {
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        }

        $entityManager->persist($user);
        $entityManager->flush();

        foreach ($competences as $competence) {
            $competenceUser = new CompetenceUser();
            $competenceUser->setUtilisateur($user);
            $competenceUser->setCompetence($competence);
            $entityManager->persist($competenceUser);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_user_list', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('user/new.html.twig', [
        'user' => $user,
        'form' => $form,
    ]);
}

    

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

	#[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
	public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
	{
		// Vérifier si l'utilisateur connecté est un administrateur
		$currentUser = $this->getUser();
	
		// Vérification si l'utilisateur est assigné à une équipe
		$isAssignedToEquipe = !$user->getEquipeUsers()->isEmpty();  // Vérifier si l'utilisateur a des équipes assignées
	
		$form = $this->createForm(UserType::class, $user, [
			'is_edit' => true,
			'is_assigned_to_team' => $isAssignedToEquipe, // Passer cette information à l'option du formulaire
		]);
		$form->handleRequest($request);
	
		// Si l'utilisateur est assigné à une équipe, on désactive la modification des compétences
		if ($isAssignedToEquipe) {
			$form->get('competences')->isDisabled(true); // Désactiver le champ 'competences'
		}
	
		if ($form->isSubmitted() && $form->isValid()) {
			// Vérification du mot de passe
			$newPassword = $form->get('plainPassword')->getData();
			if ($newPassword) {
				$user->setPassword($passwordHasher->hashPassword($user, $newPassword));
			}
	
			// Si l'utilisateur n'est pas affecté à une équipe, on gère les compétences
			if (!$isAssignedToEquipe) {
				// Supprimer les anciennes compétences
				foreach ($user->getCompetenceUsers() as $competenceUser) {
					$entityManager->remove($competenceUser);
				}
	
				// Ajouter les nouvelles compétences
				$competences = $form->get('competences')->getData();
				foreach ($competences as $competence) {
					$competenceUser = new CompetenceUser();
					$competenceUser->setUtilisateur($user);
					$competenceUser->setCompetence($competence);
					$entityManager->persist($competenceUser);
				}
			}
	
			// Enregistrer les autres modifications
			$entityManager->flush();
	
			return $this->redirectToRoute('app_user_list', [], Response::HTTP_SEE_OTHER);
		}
	
		return $this->render('user/edit.html.twig', [
			'user' => $user,
			'form' => $form->createView(),
			'isAssignedToEquipe' => $isAssignedToEquipe, // Passer l'information à la vue
		]);
		
	}
	


    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, Security $security): Response
    {
        $currentUser = $security->getUser();

        // Vérifier si l'utilisateur est authentifié et s'il essaie de supprimer son propre compte
        if ($currentUser instanceof User && $currentUser->getId() === $user->getId()) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('app_user_list');
        }

        // Vérifier si l'utilisateur essaie de supprimer un administrateur
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer un administrateur.');
            return $this->redirectToRoute('app_user_list');
        }

        // Vérifier le CSRF token
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->get('_token'))) {
            // Supprimer les relations dans la table `competence_user`
            foreach ($user->getCompetenceUsers() as $competenceUser) {
                $entityManager->remove($competenceUser);
            }

            // Supprimer les relations dans la table `equipe_user` (liens entre utilisateur et équipe)
            foreach ($user->getEquipeUsers() as $equipeUser) {
                $entityManager->remove($equipeUser);
            }

            // Vérifier si l'utilisateur est chef d'équipe, et réassigner le chef ou supprimer l'équipe si nécessaire
            foreach ($user->getEquipes() as $equipe) {
                if ($equipe->getChefEquipe() === $user) {
                    // Si l'utilisateur est le chef d'équipe, vous pouvez soit réattribuer le chef d'équipe,
                    // soit supprimer l'équipe, selon vos besoins.
                    
                    // Exemple : Réattribuer le chef d'équipe à NULL ou à un autre utilisateur
                    $equipe->setChefEquipe(null);
                    
                    // Si vous voulez supprimer l'équipe si l'utilisateur est chef :
                    // $entityManager->remove($equipe);
                }
            }

            // Supprimer l'utilisateur
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/user/edit-modal', name: 'user_edit_modal')]
    public function editModal(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new Response("Utilisateur non connecté.", Response::HTTP_UNAUTHORIZED);
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

            return new Response("Profil mis à jour avec succès.", Response::HTTP_OK);
        }

        return $this->render('user/edit_modal.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
