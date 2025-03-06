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
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->chantierRepository = static::getContainer()->get(ChantierRepository::class);
    }

    public function testIndexPageLoadsSuccessfully()
    {
        $this->client->request('GET', '/chantier/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des Chantiers');
    }

    public function testCreateNewChantier()
    {
        $crawler = $this->client->request('GET', '/chantier/new');

        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Enregistrer')->form([
            'chantier[nom]' => 'Test Chantier',
            'chantier[dateDebut]' => '2025-01-01',
            'chantier[dateFin]' => '2025-06-01',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/chantier/');

        $chantier = $this->chantierRepository->findOneBy(['nom' => 'Test Chantier']);
        $this->assertNotNull($chantier);
    }

    public function testEditChantier()
    {
        $chantier = new Chantier();
        $chantier->setLieu('Chantier Modifiable');
        $chantier->setDateDebut(new \DateTime('2025-01-01'));
        $chantier->setDateFin(new \DateTime('2025-06-01'));

        $this->entityManager->persist($chantier);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/chantier/'.$chantier->getId().'/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form([
            'chantier[nom]' => 'Chantier Modifié',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/chantier/');

        $chantierModifie = $this->chantierRepository->find($chantier->getId());
        $this->assertSame('Chantier Modifié', $chantierModifie->getNom());
    }

    public function testDeleteChantier()
    {
        $chantier = new Chantier();
        $chantier->setLieu('Chantier à supprimer');
        $chantier->setDateDebut(new \DateTime('2025-01-01'));
        $chantier->setDateFin(new \DateTime('2025-06-01'));

        $this->entityManager->persist($chantier);
        $this->entityManager->flush();

        $this->client->request('POST', '/chantier/'.$chantier->getId(), [
            '_method' => 'DELETE',
            '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('delete'.$chantier->getId()),
        ]);

        $this->assertResponseRedirects('/chantier/');
        $chantierSupprime = $this->chantierRepository->find($chantier->getId());
        $this->assertNull($chantierSupprime);
    }
}
