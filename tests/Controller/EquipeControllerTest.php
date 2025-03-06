<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Equipe;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EquipeRepository;

class EquipeControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $equipeRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $session = static::getContainer()->get('session.factory')->createSession();
        $session->start();
        static::getContainer()->set('session', $session);
        static::getContainer()->set('session', $session);

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->equipeRepository = static::getContainer()->get(EquipeRepository::class);
    }

     public function testIndexPage(): void
{
    $this->client->request('GET', '/equipe/');
    $this->client->followRedirect(); // Suivre la redirection
    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h1', '👥 Gestion des Équipes');
}

    public function testCreateEquipe(): void
{
    // 🔹 1. Charger la page du formulaire
    $crawler = $this->client->request('GET', '/equipe/new');
    $this->assertResponseIsSuccessful();
    
    // 🔹 2. Vérifier que le bouton du formulaire est bien détecté
    dump($crawler->filter('button, input[type="submit"]')->each(fn($node) => $node->text()));

    // 🔹 3. Vérifier si le bon bouton est détecté
    $button = $crawler->selectButton("Créer l'Équipe");
    if ($button->count() === 0) {
        dump('❌ Bouton non trouvé ! Vérifie son texte exact.');
        $this->fail("Bouton de soumission introuvable !");
    }

    // 🔹 4. Sélectionner et remplir le formulaire
    $form = $button->form([
        'equipe[nomEquipe]' => 'Equipe Test',
        'equipe[chefEquipe]' => 2, // Sélection d'un chef d'équipe valide
        'equipe[dateDebut]' => '2025-01-01T08:00', // Format ISO pour datetime-local
        'equipe[dateFin]' => '2025-06-01T18:00',
    ]);

    // 🔹 5. Vérifier les valeurs avant soumission
    dump($form->getValues());

    // 🔹 6. Soumettre le formulaire
    $this->client->submit($form);

    // 🔹 7. Vérifier s'il y a une erreur de validation du formulaire
    if ($this->client->getResponse()->getStatusCode() === 200) {
        dump("🛑 Le formulaire n'a pas été soumis correctement. Vérifions les erreurs.");
        dump($crawler->filter('.invalid-feedback')->each(fn($node) => $node->text()));
        $this->fail("Le formulaire n'a pas été soumis correctement.");
    }

    // 🔹 8. Vérifier que la soumission redirige bien
    $this->assertResponseRedirects('/equipe');

    // 🔹 9. Vérifier si l'équipe a bien été créée en base
    $equipe = $this->equipeRepository->findOneBy(['nomEquipe' => 'Equipe Test']);
    $this->assertNotNull($equipe);
}


   public function testShowEquipe(): void
    {
        $equipe = new Equipe();
        $equipe->setNomEquipe('Equipe Test Show');
        $equipe->setDateDebut(new \DateTime('2025-01-01'));
        $equipe->setDateFin(new \DateTime('2025-06-01'));

        $this->entityManager->persist($equipe);
        $this->entityManager->flush();

        $this->client->request('GET', '/equipe/'.$equipe->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Equipe Test Show');
    }

    public function testEditEquipe(): void
    {
        $equipe = new Equipe();
        $equipe->setNomEquipe('Equipe Test Edit');
        $equipe->setDateDebut(new \DateTime('2025-01-01'));
        $equipe->setDateFin(new \DateTime('2025-06-01'));

        $this->entityManager->persist($equipe);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/equipe/'.$equipe->getId().'/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Mettre à jour')->form([
            'equipe[nomEquipe]' => 'Equipe Modifiée'
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/equipe');

        $equipeModifiee = $this->equipeRepository->find($equipe->getId());
        $this->assertSame('Equipe Modifiée', $equipeModifiee->getNomEquipe());
    }

    /*public function testDeleteEquipe(): void
    {
        // ✅ 1. Désactiver le reboot pour conserver la session
        $this->client->disableReboot();
        
        // ✅ 2. Créer une session avant de récupérer le token CSRF
        $session = static::getContainer()->get('session.factory')->createSession();
        $session->start();
        static::getContainer()->set('session', $session);
    
        // ✅ 3. Assigner la session au client
        $this->client->getContainer()->set('session', $session);
    
        // ✅ 4. Créer une équipe fictive pour le test
        $equipe = new Equipe();
        $equipe->setNomEquipe('Equipe à Supprimer');
        $equipe->setDateDebut(new \DateTime('2025-01-01'));
        $equipe->setDateFin(new \DateTime('2025-06-01'));
    
        $this->entityManager->persist($equipe);
        $this->entityManager->flush();
    
        // ✅ 5. Vérifier que l'équipe a bien été créée
        $this->assertNotNull($this->equipeRepository->find($equipe->getId()));
    
        // ✅ 6. Récupérer le token CSRF après l'initialisation de la session
        $csrfTokenManager = static::getContainer()->get('security.csrf.token_manager');
        $csrfToken = $csrfTokenManager->getToken('delete' . $equipe->getId());
    
        // ✅ 7. Effectuer la requête DELETE avec le token CSRF
        $this->client->request('POST', '/equipe/' . $equipe->getId(), [
            '_method' => 'DELETE',
            '_token' => $csrfToken->getValue(),
        ]);
    
        // ✅ 8. Vérifier la redirection après suppression
        $this->assertResponseRedirects('/equipe');
    
        // ✅ 9. Rafraîchir l'EntityManager pour éviter le cache
        $this->entityManager->clear();
    
        // ✅ 10. Vérifier que l'équipe a bien été supprimée
        $equipeSupprimee = $this->equipeRepository->find($equipe->getId());
        $this->assertNull($equipeSupprimee, "L'équipe aurait dû être supprimée.");
    }*/
}
