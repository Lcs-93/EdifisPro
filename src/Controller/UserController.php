<?php

namespace App\Controller;

use App\Entity\CompetenceUser;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
	#[Route(name: 'app_user_index', methods: ['GET'])]
	public function index(UserRepository $userRepository): Response
	{
		return $this->render('user/index.html.twig', [
			'users' => $userRepository->findAll(),
		]);
	}

	#[Route('/user_list', name: 'app_user_list', methods: ['GET'])]
	public function list(UserRepository $userRepository): Response
	{
		return $this->render('user/list.html.twig', [
			'users' => $userRepository->findAll(),
		]);
	}

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
	        $competences = $form->get('competences')->getData();

	        $hashedPassword = $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData());
	        $user->setPassword($hashedPassword);
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
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
	        foreach ($user->getCompetenceUsers() as $competenceUser) {
		        $entityManager->remove($competenceUser);
	        }

	        $newPassword = $form->get('password')->getData();
	        if ($newPassword) {
		        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
	        }

	        $competences = $form->get('competences')->getData();
	        foreach ($competences as $competence) {
		        $competenceUser = new CompetenceUser();
		        $competenceUser->setUtilisateur($user);
		        $competenceUser->setCompetence($competence);
		        $entityManager->persist($competenceUser);
	        }

	        $entityManager->flush();

            return $this->redirectToRoute('app_user_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
	        foreach ($user->getCompetenceUsers() as $competenceUser) {
		        $entityManager->remove($competenceUser);
	        }
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_list', [], Response::HTTP_SEE_OTHER);
    }
}
