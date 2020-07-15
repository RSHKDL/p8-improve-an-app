<?php

namespace Tests\AppBundle\Tests;

use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\BaseControllerTest;

/**
 * Class SecurityControllerTest
 * @author ereshkidal
 */
class SecurityControllerTest extends BaseControllerTest
{
    public function testRegisterThenLoginNewUser(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/register');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Inscription', $this->client->getResponse()->getContent());

        $form = $crawler->selectButton('S\'inscrire')->form();
        $form['user[username]']->setValue('chewbacca');
        $form['user[plainPassword][first]']->setValue('1234');
        $form['user[plainPassword][second]']->setValue('1234');
        $form['user[email]']->setValue('chewbacca@rebel.com');
        $crawler = $this->client->submit($form);
        $this->assertStringContainsString('Superbe !', $this->client->getResponse()->getContent());

        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username']->setValue('chewbacca');
        $form['_password']->setValue('1234');
        $this->client->submit($form);
        $this->assertStringContainsString('chewbacca', $this->client->getResponse()->getContent());
        $this->assertStringContainsString('Se déconnecter', $this->client->getResponse()->getContent());
    }

    public function testCannotRegisterUserWithSameEmail(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('S\'inscrire')->form();
        $form['user[username]']->setValue('chewbacca');
        $form['user[plainPassword][first]']->setValue('1234');
        $form['user[plainPassword][second]']->setValue('1234');
        $form['user[email]']->setValue('han_solo@rebel.com');
        $this->client->submit($form);
        $this->assertStringContainsString('Il semble que vous ayez déjà un compte ici', $this->client->getResponse()->getContent());
    }

    public function testLoginWithBadCredentials(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username']->setValue('han_solo');
        $form['_password']->setValue('wrong password');
        $this->client->submit($form);
        $this->assertStringContainsString('Identifiants invalides.', $this->client->getResponse()->getContent());
    }

    public function testLoginWithGoodCredentials(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username']->setValue('han_solo');
        $form['_password']->setValue('1234');
        $this->client->submit($form);
        $this->assertStringContainsString('han_solo', $this->client->getResponse()->getContent());
    }
}
