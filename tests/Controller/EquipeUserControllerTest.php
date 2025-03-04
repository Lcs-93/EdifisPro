<?php

namespace App\Tests\Controller;

use App\Entity\EquipeUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EquipeUserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $equipeUserRepository;
    private string $path = '/equipe/user/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->equipeUserRepository = $this->manager->getRepository(EquipeUser::class);

        foreach ($this->equipeUserRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('EquipeUser index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'equipe_user[dateDebut]' => 'Testing',
            'equipe_user[dateFin]' => 'Testing',
            'equipe_user[utilisateur]' => 'Testing',
            'equipe_user[equipe]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->equipeUserRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new EquipeUser();
        $fixture->setDateDebut('My Title');
        $fixture->setDateFin('My Title');
        $fixture->setUtilisateur('My Title');
        $fixture->setEquipe('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('EquipeUser');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new EquipeUser();
        $fixture->setDateDebut('Value');
        $fixture->setDateFin('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setEquipe('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'equipe_user[dateDebut]' => 'Something New',
            'equipe_user[dateFin]' => 'Something New',
            'equipe_user[utilisateur]' => 'Something New',
            'equipe_user[equipe]' => 'Something New',
        ]);

        self::assertResponseRedirects('/equipe/user/');

        $fixture = $this->equipeUserRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getDateDebut());
        self::assertSame('Something New', $fixture[0]->getDateFin());
        self::assertSame('Something New', $fixture[0]->getUtilisateur());
        self::assertSame('Something New', $fixture[0]->getEquipe());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new EquipeUser();
        $fixture->setDateDebut('Value');
        $fixture->setDateFin('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setEquipe('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/equipe/user/');
        self::assertSame(0, $this->equipeUserRepository->count([]));
    }
}
