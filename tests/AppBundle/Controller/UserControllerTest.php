<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserControllerTest
 * @author ereshkidal
 */
class UserControllerTest extends BaseControllerTest
{
    public function testOnlyAdminCanSeeUserList()
    {
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());
        $this->client->request('GET', '/users');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));
        $this->client->request('GET', '/users');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testOnlyLoggedInUsersCanSeeTheirProfile()
    {
        $this->client->request('GET', '/profile');
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());
        $this->client->request('GET', '/profile');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Votre profil', $this->client->getResponse()->getContent());
        $this->assertContains('han_solo', $this->client->getResponse()->getContent());
    }

    public function testOnlyAdminCanCreateNewUsers()
    {
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());
        $this->client->request('GET', '/users/create');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));
        $this->client->request('GET', '/users/create');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanEditSelf()
    {
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());
        $this->client->request('GET', '/users/2/edit');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());
        $crawler = $this->client->request('GET', '/users/1/edit');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('han_solo@rebel.com', $this->client->getResponse()->getContent());

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[email]']->setValue('chewbaca@rebel.org');
        $crawler = $this->client->submit($form);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("han_solo")')->count());
        $this->assertContains('chewbaca@rebel.org', $this->client->getResponse()->getContent());
    }

    public function testAdminCanEditAllUsers()
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));
        $crawler = $this->client->request('GET', '/users/2/edit');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('luke_skywalker@rebel.com', $this->client->getResponse()->getContent());

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[email]']->setValue('chewbaca@rebel.org');
        $form['user[roles]'] = [User::ROLE_ADMIN];
        $this->client->submit($form);
        $this->assertContains('chewbaca@rebel.org', $this->client->getResponse()->getContent());

        $crawler = $this->client->request('GET', '/users');
        $this->assertEquals(2, $crawler->filter('td:contains("Administrateur")')->count());
    }

    public function testOnlyAdminCanDeleteAllUsers()
    {
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());
        $this->client->request('GET', '/users/2/delete');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));
        $this->client->request('GET', '/users/1/delete');
        $this->client->request('GET', '/users/2/delete');
        $this->assertContains('darth_vader', $this->client->getResponse()->getContent());
        $this->assertNotContains('han_solo', $this->client->getResponse()->getContent());
        $this->assertNotContains('luke_skywalker', $this->client->getResponse()->getContent());
    }

    /**
     * @todo test skipped because feature not implemented yet
     */
    public function testAdminCannotDeleteSelf()
    {
        $this->markTestSkipped('Should be implemented and tested');
    }
}
