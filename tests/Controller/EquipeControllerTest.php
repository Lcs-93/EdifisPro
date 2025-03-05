<?php

namespace App\Tests\Controller;

use App\Entity\Equipe;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EquipeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $equipeRepository;
    private string $path = '/equipe/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->equipeRepository = $this->manager->getRepository(Equipe::class);

        foreach ($this->equipeRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Equipe index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'equipe[nomEquipe]' => 'Testing',
            'equipe[chefEquipe]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->equipeRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Equipe();
        $fixture->setNomEquipe('My Title');
        $fixture->setChefEquipe('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Equipe');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Equipe();
        $fixture->setNomEquipe('Value');
        $fixture->setChefEquipe('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'equipe[nomEquipe]' => 'Something New',
            'equipe[chefEquipe]' => 'Something New',
        ]);

        self::assertResponseRedirects('/equipe/');

        $fixture = $this->equipeRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNomEquipe());
        self::assertSame('Something New', $fixture[0]->getChefEquipe());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Equipe();
        $fixture->setNomEquipe('Value');
        $fixture->setChefEquipe('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/equipe/');
        self::assertSame(0, $this->equipeRepository->count([]));
    }
}
