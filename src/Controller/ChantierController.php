<?php
namespace App\Controller;

use App\Entity\Affectation;
use App\Entity\Chantier;
use App\Entity\CompetenceChantier;
use App\Form\ChantierType;
use App\Repository\ChantierRepository;

use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/chantier')]
class ChantierController extends AbstractController
{

    #[Route('/api/chantiers', name: 'api_chantiers', methods: ['GET'])]
    public function getChantiers(ChantierRepository $chantierRepository): JsonResponse
    {
        $chantiers = $chantierRepository->findAll();
        
        if (!$chantiers) {
            return new JsonResponse(["message" => "Aucun chantier trouvé"], 404);
        }

        $chantierData = [];
        foreach ($chantiers as $chantier) {
            $equipes = [];
            foreach ($chantier->getAffectations() as $affectation) {
                if ($affectation->getEquipe()) {
                    $equipes[] = $affectation->getEquipe()->getNomEquipe();
                }
            }

            $chantierData[] = [
                'id' => $chantier->getId(),
                'lieu' => $chantier->getLieu(),
                'status' => $chantier->getStatus(),
                'dateDebut' => $chantier->getDateDebut()->format('Y-m-d'),
                'dateFin' => $chantier->getDateFin()->format('Y-m-d'),
                'equipes' => $equipes,
            ];
        }

        return new JsonResponse($chantierData);
    }



    #[Route('/new', name: 'app_chantier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, EquipeRepository $equipeRepo): Response
    {
        $chantier = new Chantier();
        $form = $this->createForm(ChantierType::class, $chantier);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $dateDebutChantier = $chantier->getDateDebut();
            $dateFinChantier = $chantier->getDateFin();
            $equipes = $form->get('equipes')->getData();
            $competencesRequises = $form->get('competences')->getData(); // ⚠ Récupération des compétences
    
            $validAffectation = true;
    
            foreach ($equipes as $equipe) {
                $dateDebutEquipe = $equipe->getDateDebut();
                $dateFinEquipe = $equipe->getDateFin();
    
                // Vérification des dates
                if ($dateDebutEquipe > $dateFinChantier || $dateFinEquipe < $dateDebutChantier) {
                    $validAffectation = false;
                    $this->addFlash(
                        'danger',
                        "⚠ L'équipe '{$equipe->getNomEquipe()}' ne peut pas être affectée à ce chantier en raison des dates."
                    );
                    continue;
                }
    
                // Vérification des compétences
                $users = $equipe->getEquipeUsers()->map(fn($equipeUser) => $equipeUser->getUtilisateur());
                $competencesEquipe = [];
    
                foreach ($users as $user) {
                    foreach ($user->getCompetenceUsers() as $competenceUser) {
                        $competencesEquipe[] = $competenceUser->getCompetence();
                    }
                }
    
   
$competencesEquipeIds = array_map(fn($c) => $c->getId(), ($competencesEquipe instanceof \Doctrine\Common\Collections\Collection) ? $competencesEquipe->toArray() : $competencesEquipe);
$competencesRequisesIds = array_map(fn($c) => $c->getId(), ($competencesRequises instanceof \Doctrine\Common\Collections\Collection) ? $competencesRequises->toArray() : $competencesRequises);

if (!array_intersect($competencesRequisesIds, $competencesEquipeIds)) {
    $validAffectation = false;
    $this->addFlash(
        'danger',
        "⚠ L'équipe '{$equipe->getNomEquipe()}' ne possède pas les compétences requises pour ce chantier."
    );
}

            }
    
            if (!$validAffectation) {
                return $this->redirectToRoute('chantier_index' // ou 'chantier_list' selon ton routeur
, [
                    'chantier' => $chantier,
                    'form' => $form->createView(),
                ]);
            }
    
            // Enregistrement du chantier
            $em->persist($chantier);
            $em->flush();
    
            // 🔥 Ajouter explicitement les compétences dans `competence_chantier`
            foreach ($competencesRequises as $competence) {
                $competenceChantier = new CompetenceChantier();
                $competenceChantier->setChantier($chantier);
                $competenceChantier->setCompetence($competence);
                $em->persist($competenceChantier);
            }
    
            // 🔥 Enregistrer les affectations d'équipes
            foreach ($equipes as $equipe) {
                $affectation = new Affectation();
                $affectation->setChantier($chantier);
                $affectation->setEquipe($equipe);
                $affectation->setDateDebut(max($dateDebutEquipe, $dateDebutChantier));
                $affectation->setDateFin(min($dateFinEquipe, $dateFinChantier));
                $em->persist($affectation);
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
    }#[Route('/{id}/edit', name: 'app_chantier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chantier $chantier, EntityManagerInterface $em, EquipeRepository $equipeRepo): Response
    {
        $form = $this->createForm(ChantierType::class, $chantier);
    
        // Récupérer les équipes actuellement affectées
        $currentEquipes = [];
        foreach ($chantier->getAffectations() as $affectation) {
            $currentEquipes[] = $affectation->getEquipe();
        }
        $form->get('equipes')->setData($currentEquipes);
    
        // Récupérer les compétences actuellement affectées
        $currentCompetences = [];
        foreach ($chantier->getCompetenceChantiers() as $competenceChantier) {
            $currentCompetences[] = $competenceChantier->getCompetence();
        }
        $form->get('competences')->setData($currentCompetences);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $dateDebutChantier = $chantier->getDateDebut();
            $dateFinChantier = $chantier->getDateFin();
            $selectedEquipes = $form->get('equipes')->getData();
            $selectedCompetences = $form->get('competences')->getData();
    
            $validAffectation = true;
    
            // 🔹 Vérification des dates pour les équipes sélectionnées
            foreach ($selectedEquipes as $equipe) {
                $dateDebutEquipe = $equipe->getDateDebut();
                $dateFinEquipe = $equipe->getDateFin();
    
                if ($dateDebutEquipe > $dateFinChantier || $dateFinEquipe < $dateDebutChantier) {
                    $validAffectation = false;
                    $this->addFlash(
                        'danger',
                        "⚠ L'équipe '{$equipe->getNomEquipe()}' ne peut pas être affectée car ses dates ne correspondent pas au chantier."
                    );
                    continue;
                }
    
                // 🔹 Vérification des compétences des équipes sélectionnées
                $users = $equipe->getEquipeUsers()->map(fn($equipeUser) => $equipeUser->getUtilisateur());
                $competencesEquipe = [];
    
                foreach ($users as $user) {
                    foreach ($user->getCompetenceUsers() as $competenceUser) {
                        $competencesEquipe[] = $competenceUser->getCompetence();
                    }
                }
    
                $competencesEquipeIds = array_map(fn($c) => $c->getId(), ($competencesEquipe instanceof \Doctrine\Common\Collections\Collection) ? $competencesEquipe->toArray() : $competencesEquipe);
                $competencesRequisesIds = array_map(fn($c) => $c->getId(), ($selectedCompetences instanceof \Doctrine\Common\Collections\Collection) ? $selectedCompetences->toArray() : $selectedCompetences);
    
                if (!array_intersect($competencesRequisesIds, $competencesEquipeIds)) {
                    $validAffectation = false;
                    $this->addFlash(
                        'danger',
                        "⚠ L'équipe '{$user->getNomUser()}' ne possède pas les compétences requises."
                    );
                }
            }
    
            // 🔹 Vérification si une compétence essentielle est retirée
            $competencesEssentielles = [];
            foreach ($currentEquipes as $equipe) {
                $users = $equipe->getEquipeUsers()->map(fn($equipeUser) => $equipeUser->getUtilisateur());
                foreach ($users as $user) {
                    foreach ($user->getCompetenceUsers() as $competenceUser) {
                        if (!in_array($competenceUser->getCompetence(), $selectedCompetences)) {
                            $competencesEssentielles[] = $competenceUser->getCompetence();
                        }
                    }
                }
            }
    
            if (!empty($competencesEssentielles)) {
                $validAffectation = false;
                $this->addFlash(
                    'danger',
                    "⚠ Les compétences suivantes sont essentielles pour au moins un membre d'une équipe et ne peuvent être supprimées : " .
                    implode(', ', array_map(fn($c) => $c->getNomCompetence(), $competencesEssentielles))
                );
            }
    
            if (!$validAffectation) {
                return $this->render('chantier/edit.html.twig', [
                    'chantier' => $chantier,
                    'form' => $form->createView(),
                ]);
            }
    
            // 🔥 Mise à jour des affectations
            foreach ($chantier->getAffectations() as $affectation) {
                $em->remove($affectation);
            }
            foreach ($selectedEquipes as $equipe) {
                $affectation = new Affectation();
                $affectation->setChantier($chantier);
                $affectation->setEquipe($equipe);
                $affectation->setDateDebut($chantier->getDateDebut());
                $affectation->setDateFin($chantier->getDateFin());
                $em->persist($affectation);
            }
    
            // 🔥 Mise à jour des compétences du chantier
            foreach ($chantier->getCompetenceChantiers() as $competenceChantier) {
                $em->remove($competenceChantier);
            }
            foreach ($selectedCompetences as $competence) {
                $competenceChantier = new CompetenceChantier();
                $competenceChantier->setChantier($chantier);
                $competenceChantier->setCompetence($competence);
                $em->persist($competenceChantier);
            }
    
            $em->flush();
    
            return $this->redirectToRoute('app_chantier_index');
        }
    
        return $this->render('chantier/edit.html.twig', [
            'chantier' => $chantier, // ✅ S'assurer que l'objet est bien passé
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
    if ($this->isCsrfTokenValid('delete'.$chantier->getId(), $request->request->get('_token'))) {
        foreach ($chantier->getCompetenceChantiers() as $competenceChantier) {
            $entityManager->remove($competenceChantier);
        }

        $entityManager->remove($chantier);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_chantier_index');
}



}
