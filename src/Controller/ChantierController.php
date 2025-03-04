<?php

namespace App\Controller;

use App\Entity\Chantier;
use App\Entity\Competence;
use App\Entity\CompetenceChantier;
use App\Form\ChantierType;
use App\Repository\ChantierRepository;
use App\Repository\CompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/chantier')]
class ChantierController extends AbstractController
{
    #[Route('/new', name: 'app_chantier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, CompetenceRepository $competenceRepo): Response
    {
        $chantier = new Chantier();
        $form = $this->createForm(ChantierType::class, $chantier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $competences = $form->get('competences')->getData();

            $em->persist($chantier);
            $em->flush();

            foreach ($competences as $competence) {
                $competenceChantier = new CompetenceChantier();
                $competenceChantier->setChantier($chantier);
                $competenceChantier->setCompetence($competence);
                $em->persist($competenceChantier);
            }

            $em->flush();

            return $this->redirectToRoute('app_chantier_index');
        }

        return $this->render('chantier/new.html.twig', [
            'chantier' => $chantier,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_chantier_show', methods: ['GET'])]
    public function show(Chantier $chantier): Response
    {
        return $this->render('chantier/show.html.twig', [
            'chantier' => $chantier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_chantier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chantier $chantier, EntityManagerInterface $em, CompetenceRepository $competenceRepo): Response
    {
        $form = $this->createForm(ChantierType::class, $chantier);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($chantier->getCompetenceChantiers() as $competenceChantier) {
                $em->remove($competenceChantier);
            }
    
            $competences = $form->get('competences')->getData();
            
            foreach ($competences as $competence) {
                $competenceChantier = new CompetenceChantier();
                $competenceChantier->setChantier($chantier);
                $competenceChantier->setCompetence($competence);
                $em->persist($competenceChantier);
            }
    
            $em->flush();
    
            return $this->redirectToRoute('app_chantier_index');
        }
    
        return $this->render('chantier/edit.html.twig', [
            'chantier' => $chantier,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/', name: 'app_chantier_index', methods: ['GET'])]
public function index(ChantierRepository $chantierRepository): Response
{
    return $this->render('chantier/index.html.twig', [
        'chantiers' => $chantierRepository->findAll(),
    ]);
}


    #[Route('/{id}', name: 'app_chantier_delete', methods: ['POST'])]
    public function delete(Request $request, Chantier $chantier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$chantier->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($chantier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_chantier_index', [], Response::HTTP_SEE_OTHER);
    }
}
