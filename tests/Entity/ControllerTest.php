<?php


namespace App\Tests\Entity;


use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class ControllerTest extends WebTestCase
{

    public function testLogin()
    {
        // Vérification si accès a la page LOGIN
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRedirectIfNotLogged()
    {
        // Vérification si redirection si non connecté
        $client = static::createClient();
        $client->request('GET', '/admin/user');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testConnexion()
    {
        // Vérification de la connexion avec un user et redirection
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email'=>'ralpina@hotmail.com']);
        $client->loginUser($testUser);
        $client->request('GET', '/admin/user');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $client->request('GET', '/admin/produit');
        $this->assertTrue($client->getResponse()->isSuccessful());

    }
}