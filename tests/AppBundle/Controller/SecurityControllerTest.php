<?php

namespace Tests\AppBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SecurityControllerTest
 * @author ereshkidal
 */
class SecurityControllerTest extends WebTestCase
{
    public function testRegister()
    {
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/register');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('Ajouter')->form();

        $form['user[username]']->setValue('John');
        $form['user[password][first]']->setValue('1234');
        $form['user[password][second]']->setValue('1234');
        $form['user[email]']->setValue('john@mail.com');
        
        $client->submit($form);
        $this->assertContains('Superbe !', $client->getResponse()->getContent());
    }

    public function testLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        //$this->assertContains('Hello World', $crawler->filter('h1')->text());
    }
}
