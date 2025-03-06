<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testLoginPageIsSuccessful(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Se connecter');
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $crawler = $this->client->request('GET', '/login');
        
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'fakeuser',
            'password' => 'wrongpassword',
        ]);

        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }

   public function testLoginWithValidCredentials(): void
    {
        // 1️Accéder à la page de connexion
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Se connecter'); // Vérifie que la page de login est bien affichée
    
        // Soumettre le formulaire de connexion
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'admin@admin.com',  // Remplace par un email valide dans ta base de test
            'password' => 'adminpassword',  // Remplace par le bon mot de passe
        ]);
        
        $this->client->submit($form);
    
        //  Vérifier la redirection après connexion
        $this->assertResponseRedirects('/user');
        // Suivre la redirection pour arriver sur la page d'accueil
        $crawler = $this->client->followRedirect();
        dump($crawler->html()); // DEBUG : Vérifie que la page est bien celle attendue après login
    
        // Vérifier si l'utilisateur est bien connecté
        $this->assertNotNull(static::getContainer()->get('security.token_storage')->getToken());
        $this->assertTrue(static::getContainer()->get('security.authorization_checker')->isGranted('ROLE_USER'));
    
        // Vérifier la présence du bouton de déconnexion
        $this->assertSelectorExists('a[href="/logout"]', 'Déconnexion'); // Vérifie que le texte est bien présent
    }
    

    public function testLogout(): void
    {
        $this->client->request('GET', '/logout');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }
}