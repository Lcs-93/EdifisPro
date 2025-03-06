<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Chantier;
use App\Repository\ChantierRepository;
use Doctrine\ORM\EntityManagerInterface;

class ChantierControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $chantierRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient([], [
            'HTTP_HOST' => 'localhost'
        ]);
        $this->client->disableReboot(); // Empêche la réinitialisation de la session
        $this->client->request('GET', '/'); // Initie une session
        parent::setUp(); // Important pour éviter des problèmes d'initialisation

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->chantierRepository = static::getContainer()->get(ChantierRepository::class);
    }


    /**
     * Teste si la page d'index des chantiers se charge correctement
     */
    public function testIndexPageLoadsSuccessfully()
    {
        $this->client->request('GET', '/chantier/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'construction Gestion des Chantiers');
    }

    /**
     * Teste la création d'un nouveau chantier via le formulaire
     */
    public function testCreateNewChantier()
    {
        // Récupérer la page de création d'un chantier
        $crawler = $this->client->request('GET', '/chantier/new');
        $this->assertResponseIsSuccessful();

        // Vérifier la présence du token CSRF
        $csrfToken = $crawler->filter('input[name="chantier[_token]"]')->attr('value');
        $this->assertNotEmpty($csrfToken, "Le token CSRF doit être présent");

        // Soumettre le formulaire avec des données valides
        $form = $crawler->selectButton('Créer le Chantier')->form([
            'chantier[lieu]' => 'Test Chantier',
            'chantier[dateDebut]' => '2025-01-01T00:00',
            'chantier[dateFin]' => '2025-06-01T00:00',
            'chantier[status]' => 'en_cours',
            'chantier[_token]' => $csrfToken
        ]);

        $this->client->submit($form);

        // Vérifier si la réponse est une redirection (code 302)
        $this->assertResponseRedirects('/chantier/', 302, "La page doit rediriger après la création du chantier");

        // Suivre la redirection
        $this->client->followRedirect();

        // Vérifier que la page de destination est bien un succès (code 200)
        $this->assertResponseIsSuccessful();

        // Vérifier que le chantier a bien été ajouté en base de données
        $chantierRepository = static::getContainer()->get(ChantierRepository::class);
        $chantier = $chantierRepository->findOneBy(['lieu' => 'Test Chantier']);
        $this->assertNotNull($chantier, "Le chantier doit être créé en base de données.");
    }




    /**
     * Teste la modification d'un chantier existant
     */
    public function testEditChantier()
    {
        // Vérifie que l'EntityManager est bien initialisé
        $this->assertNotNull($this->entityManager, "EntityManager ne doit pas être null");

        // Création d'un chantier en base
        $chantier = new Chantier();
        $chantier->setLieu('Chantier Modifiable');
        $chantier->setDateDebut(new \DateTime('2025-01-01'));
        $chantier->setDateFin(new \DateTime('2025-06-01'));
        $chantier->setStatus('en_cours');

        // Persistance du chantier
        $this->entityManager->persist($chantier);
        $this->entityManager->flush();

        // Vérification que le chantier a bien été enregistré
        $chantier = $this->chantierRepository->findOneBy(['lieu' => 'Chantier Modifiable']);
        $this->assertNotNull($chantier, "Le chantier doit être trouvé en base.");

        // Accéder à la page d'édition
        $crawler = $this->client->request('GET', '/chantier/' . $chantier->getId() . '/edit');
        $this->assertResponseIsSuccessful();

        // Modification du chantier via le formulaire
        $form = $crawler->selectButton('Mettre à Jour')->form([
            'chantier[lieu]' => 'Lieu Modifié',
        ]);

        // Soumission du formulaire
        $this->client->submit($form);
        $this->assertResponseRedirects('/chantier/');

        // Vérification après redirection
        $this->client->followRedirect();
        $chantierModifie = $this->chantierRepository->find($chantier->getId());
        $this->assertSame('Lieu Modifié', $chantierModifie->getLieu(), "Le chantier doit être modifié.");
    }

    /**
     * Teste la suppression d'un chantier
     */
    /*public function testDeleteChantier()
    {
        $chantier = new Chantier();
        $chantier->setLieu('Chantier à supprimer');
        $chantier->setDateDebut(new \DateTime('2025-01-01'));
        $chantier->setDateFin(new \DateTime('2025-06-01'));
        $chantier->setStatus('en_cours');

        $this->entityManager->persist($chantier);
        $this->entityManager->flush();

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('delete' . $chantier->getId());
        $this->client->request('POST', '/chantier/' . $chantier->getId(), [
            '_method' => 'DELETE',
            '_token' => $csrfToken,
        ]);

        $this->assertResponseRedirects('/chantier/');
        $chantierSupprime = $this->chantierRepository->find($chantier->getId());
        $this->assertNull($chantierSupprime);
    }*/
}
