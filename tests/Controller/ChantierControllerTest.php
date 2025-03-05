<?php

namespace App\Tests\Controller;

use App\Entity\Chantier;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ChantierControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $chantierRepository;
    private string $path = '/chantier/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->chantierRepository = $this->manager->getRepository(Chantier::class);

        // Suppression de tous les chantiers pour partir sur une base propre
        foreach ($this->chantierRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', $this->path);
        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Chantier index');
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Enregistrer le chantier', [
            'chantier[lieu]' => 'Test Lieu',
            'chantier[dateDebut]' => '2025-03-01 08:00:00',
            'chantier[dateFin]' => '2025-03-05 18:00:00',
            'chantier[status]' => 'en_cours',
        ]);

        self::assertResponseRedirects($this->path);
        self::assertSame(1, $this->chantierRepository->count([]));
    }

    public function testShow(): void
    {
        $chantier = new Chantier();
        $chantier->setLieu('Test Lieu');
        $chantier->setDateDebut(new \DateTime('2025-03-01 08:00:00'));
        $chantier->setDateFin(new \DateTime('2025-03-05 18:00:00'));
        $chantier->setStatus('en_cours');

        $this->manager->persist($chantier);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $chantier->getId()));
        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Chantier');

        self::assertSelectorTextContains('td', 'Test Lieu');
    }

    public function testEdit(): void
    {
        $chantier = new Chantier();
        $chantier->setLieu('Ancien Lieu');
        $chantier->setDateDebut(new \DateTime('2025-03-01 08:00:00'));
        $chantier->setDateFin(new \DateTime('2025-03-05 18:00:00'));
        $chantier->setStatus('en_cours');

        $this->manager->persist($chantier);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $chantier->getId()));
        $this->client->submitForm('Enregistrer le chantier', [
            'chantier[lieu]' => 'Nouveau Lieu',
            'chantier[dateDebut]' => '2025-03-10 08:00:00',
            'chantier[dateFin]' => '2025-03-15 18:00:00',
            'chantier[status]' => 'termine',
        ]);

        self::assertResponseRedirects($this->path);

        $updatedChantier = $this->chantierRepository->find($chantier->getId());
        self::assertSame('Nouveau Lieu', $updatedChantier->getLieu());
        self::assertEquals(new \DateTime('2025-03-10 08:00:00'), $updatedChantier->getDateDebut());
        self::assertEquals(new \DateTime('2025-03-15 18:00:00'), $updatedChantier->getDateFin());
        self::assertSame('termine', $updatedChantier->getStatus());
    }

    public function testRemove(): void
    {
        $chantier = new Chantier();
        $chantier->setLieu('Lieu à supprimer');
        $chantier->setDateDebut(new \DateTime('2025-03-01 08:00:00'));
        $chantier->setDateFin(new \DateTime('2025-03-05 18:00:00'));
        $chantier->setStatus('en_cours');

        $this->manager->persist($chantier);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $chantier->getId()));
        $this->client->submitForm('Supprimer');

        self::assertResponseRedirects($this->path);
        self::assertSame(0, $this->chantierRepository->count([]));
    }
}
