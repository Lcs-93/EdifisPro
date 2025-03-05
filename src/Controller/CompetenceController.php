<?php
namespace App\Controller;

use App\Entity\Competence;
use App\Form\CompetenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompetenceController extends AbstractController
{
    #[Route('/admin/competence/new', name: 'app_competence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Vérifier si l'utilisateur a un rôle admin
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home'); // Redirection si l'utilisateur n'est pas admin
        }

        $competence = new Competence();
        $form = $this->createForm(CompetenceType::class, $competence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nomCompetence = $competence->getNomCompetence();

            // Vérifier si la compétence existe déjà
            $existingCompetence = $em->getRepository(Competence::class)->findOneBy(['nomCompetence' => $nomCompetence]);

            if ($existingCompetence) {
                // La compétence existe déjà, on ajoute un message d'erreur
                $this->addFlash('error', 'Cette compétence existe déjà.');

                // Renvoyer l'utilisateur à la page de création avec les données du formulaire
                return $this->redirectToRoute('app_competence_new');
            }

            // Sauvegarder la nouvelle compétence si elle n'existe pas déjà
            $em->persist($competence);
            $em->flush();

            $this->addFlash('success', 'Compétence ajoutée avec succès.');

            return $this->redirectToRoute('app_competence_index'); // Redirection vers la liste des compétences
        }

        return $this->render('competence/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/competence', name: 'app_competence_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        // Vérifier si l'utilisateur a un rôle admin
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home'); // Redirection si l'utilisateur n'est pas admin
        }

        $competences = $em->getRepository(Competence::class)->findAll();

        return $this->render('competence/index.html.twig', [
            'competences' => $competences,
        ]);
    }

    #[Route('/admin/competence/{id}', name: 'app_competence_show', methods: ['GET'])]
    public function show(Competence $competence): Response
    {
        return $this->render('competence/show.html.twig', [
            'competence' => $competence,
        ]);
    }

    #[Route('/admin/competence/{id}/edit', name: 'app_competence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Competence $competence, EntityManagerInterface $em): Response
    {
        // Vérification si l'utilisateur est admin
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home'); // Redirection si l'utilisateur n'est pas admin
        }
    
        $form = $this->createForm(CompetenceType::class, $competence);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $nomCompetence = $competence->getNomCompetence();
    
            // Vérifier si la compétence existe déjà (même nom)
            $existingCompetence = $em->getRepository(Competence::class)->findOneBy(['nomCompetence' => $nomCompetence]);
    
            if ($existingCompetence && $existingCompetence->getId() !== $competence->getId()) {
                // Si une compétence avec le même nom existe déjà, on affiche un message d'erreur
                $this->addFlash('error', 'Cette compétence existe déjà.');
    
                return $this->redirectToRoute('app_competence_edit', ['id' => $competence->getId()]);
            }
    
            $em->flush();
    
            $this->addFlash('success', 'Compétence mise à jour avec succès.');
    
            return $this->redirectToRoute('app_competence_index'); // Redirection vers la liste des compétences
        }
    
        return $this->render('competence/edit.html.twig', [
            'form' => $form->createView(),
            'button_label' => 'Mettre à jour', // Modifier le texte du bouton
        ]);
    }
    

    #[Route('/admin/competence/{id}/delete', name: 'app_competence_delete', methods: ['POST'])]
    public function delete(Request $request, Competence $competence, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $competence->getId(), $request->request->get('_token'))) {
            $em->remove($competence);
            $em->flush();
            $this->addFlash('success', 'Compétence supprimée avec succès.');
        }

        return $this->redirectToRoute('app_competence_index');
    }
}


