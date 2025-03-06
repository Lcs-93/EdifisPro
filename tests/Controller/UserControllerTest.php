<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $userRepository;
    private $passwordHasher;
    private $csrfTokenManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->csrfTokenManager = static::getContainer()->get(CsrfTokenManagerInterface::class);

        // 🔹 Activation de la session pour éviter l'erreur CSRF
        $session = static::getContainer()->get('session.factory')->createSession();
        $session->set('test_session', true);
        $session->save();
        $this->client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie($session->getName(), $session->getId()));
    }

    public function testUserDashboardRedirectionIfNotAuthenticated(): void
    {
        $this->client->request('GET', '/user');
        $this->assertResponseRedirects('/login');
    }

    public function testUserDashboardAccessWithAuthentication(): void
    {
        $user = $this->createUser('user1@example.com', 'password', 'Test', 'User', ['ROLE_USER']);
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/user');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Bienvenue sur votre espace');
    }

    public function testUserListPage(): void
    {
        $user = $this->createUser('admin@example.com', 'password', 'Admin', 'User', ['ROLE_ADMIN']);
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/user/user_list');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
        $this->assertGreaterThan(0, $crawler->filter('table tbody tr')->count());
    }

    public function testCreateNewUser(): void
    {
        $user = $this->createUser('admin@example.com', 'password', 'Admin', 'User', ['ROLE_ADMIN']);
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/user/new');
        $this->assertResponseIsSuccessful();


        // 🔹 Sélection du bon bouton "Enregistrer"
        $button = $crawler->selectButton('Enregistrer');
        $this->assertNotNull($button, "Le bouton 'Enregistrer' n'existe pas !");
        
        $form = $button->form([
            'user[nom]' => 'New User',
            'user[prenom]' => 'Created',
            'user[email]' => 'newuser@example.com',
            'user[plainPassword]' => 'newpassword',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/user/user_list');

        $newUser = $this->userRepository->findOneBy(['email' => 'newuser@example.com']);
        $this->assertNotNull($newUser);
    }

   public function testEditUser(): void
    {
        $admin = $this->createUser('admin@example.com', 'password', 'Admin', 'User', ['ROLE_ADMIN']);
        $this->client->loginUser($admin);

        $userToEdit = $this->createUser('edituser@example.com', 'password', 'Edit', 'User', ['ROLE_USER']);
        $crawler = $this->client->request('GET', '/user/' . $userToEdit->getId() . '/edit');

        $this->assertResponseIsSuccessful();


        $button = $crawler->selectButton('Mettre à Jour');
        $this->assertNotNull($button, "Le bouton 'Enregistrer' n'existe pas !");
        
        $form = $button->form([
            'user[nom]' => 'Edited User',
            'user[prenom]' => 'Updated',
            'user[email]' => 'edited@example.com',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/user/user_list');

        $updatedUser = $this->userRepository->findOneBy(['email' => 'edited@example.com']);
        $this->assertNotNull($updatedUser);
    }

    private function createUser(string $email, string $plainPassword, string $nom, string $prenom, array $roles = ['ROLE_USER']): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setRoles($roles);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
