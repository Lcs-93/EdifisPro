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

        foreach ($this->chantierRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Chantier index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'chantier[lieu]' => 'Testing',
            'chantier[dateDebut]' => 'Testing',
            'chantier[dateFin]' => 'Testing',
            'chantier[status]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->chantierRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Chantier();
        $fixture->setLieu('My Title');
        $fixture->setDateDebut('My Title');
        $fixture->setDateFin('My Title');
        $fixture->setStatus('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Chantier');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Chantier();
        $fixture->setLieu('Value');
        $fixture->setDateDebut('Value');
        $fixture->setDateFin('Value');
        $fixture->setStatus('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'chantier[lieu]' => 'Something New',
            'chantier[dateDebut]' => 'Something New',
            'chantier[dateFin]' => 'Something New',
            'chantier[status]' => 'Something New',
        ]);

        self::assertResponseRedirects('/chantier/');

        $fixture = $this->chantierRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getLieu());
        self::assertSame('Something New', $fixture[0]->getDateDebut());
        self::assertSame('Something New', $fixture[0]->getDateFin());
        self::assertSame('Something New', $fixture[0]->getStatus());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Chantier();
        $fixture->setLieu('Value');
        $fixture->setDateDebut('Value');
        $fixture->setDateFin('Value');
        $fixture->setStatus('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/chantier/');
        self::assertSame(0, $this->chantierRepository->count([]));
    }
}
