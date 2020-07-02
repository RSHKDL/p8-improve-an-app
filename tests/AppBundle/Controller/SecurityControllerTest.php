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
    public function testRegisterThenLoginNewUser()
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/register');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Inscription', $this->client->getResponse()->getContent());

        $form = $crawler->selectButton('S\'inscrire')->form();
        $form['user[username]']->setValue('chewbacca');
        $form['user[plainPassword][first]']->setValue('1234');
        $form['user[plainPassword][second]']->setValue('1234');
        $form['user[email]']->setValue('chewbacca@rebel.com');
        $crawler = $this->client->submit($form);
        $this->assertContains('Superbe !', $this->client->getResponse()->getContent());

        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username']->setValue('chewbacca');
        $form['_password']->setValue('1234');
        $this->client->submit($form);
        $this->assertContains('chewbacca', $this->client->getResponse()->getContent());
        $this->assertContains('Se déconnecter', $this->client->getResponse()->getContent());
    }

    public function testCannotRegisterUserWithSameEmail()
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('S\'inscrire')->form();
        $form['user[username]']->setValue('chewbacca');
        $form['user[plainPassword][first]']->setValue('1234');
        $form['user[plainPassword][second]']->setValue('1234');
        $form['user[email]']->setValue('han_solo@rebel.com');
        $this->client->submit($form);
        $this->assertContains('Il semble que vous ayez déjà un compte ici', $this->client->getResponse()->getContent());
    }

    public function testLoginWithBadCredentials()
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username']->setValue('han_solo');
        $form['_password']->setValue('wrong password');
        $this->client->submit($form);
        $this->assertContains('Identifiants invalides.', $this->client->getResponse()->getContent());
    }

    public function testLoginWithGoodCredentials()
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username']->setValue('han_solo');
        $form['_password']->setValue('1234');
        $this->client->submit($form);
        $this->assertContains('han_solo', $this->client->getResponse()->getContent());
    }
}
