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
    public function testOnlyAdminCanSeeUserList(): void
    {
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());
        $this->client->request('GET', '/users');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));
        $this->client->request('GET', '/users');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testOnlyLoggedInUsersCanSeeTheirProfile(): void
    {
        $this->client->request('GET', '/profile');
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());
        $this->client->request('GET', '/profile');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Votre profil', $this->client->getResponse()->getContent());
        $this->assertStringContainsString('han_solo', $this->client->getResponse()->getContent());
    }

    public function testOnlyAdminCanCreateNewUsers(): void
    {
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());
        $this->client->request('GET', '/users/create');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));
        $crawler = $this->client->request('GET', '/users/create');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Créer un utilisateur')->form();
        $form['user[username]']->setValue('yoda');
        $form['user[email]']->setValue('yoda@jedi.org');
        $form['user[plainPassword][first]']->setValue('1234');
        $form['user[plainPassword][second]']->setValue('1234');
        $form['user[role]']->setValue(User::ROLE_USER);
        $this->client->submit($form);
        $this->assertStringContainsString('L&#039;utilisateur yoda a bien été créé !', $this->client->getResponse()->getContent());
        $this->assertStringContainsString('yoda@jedi.org', $this->client->getResponse()->getContent());
    }

    public function testUserCanEditSelf(): void
    {
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());
        $this->client->request('GET', '/users/2/edit');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());
        $crawler = $this->client->request('GET', '/users/1/edit');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('han_solo@rebel.com', $this->client->getResponse()->getContent());

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[email]']->setValue('chewbacca@rebel.org');
        $crawler = $this->client->submit($form);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("han_solo")')->count());
        $this->assertStringContainsString('chewbacca@rebel.org', $this->client->getResponse()->getContent());
    }

    public function testAdminCanEditAllUsers(): void
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));
        $crawler = $this->client->request('GET', '/users/2/edit');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('luke_skywalker@rebel.com', $this->client->getResponse()->getContent());

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[email]']->setValue('chewbacca@rebel.org');
        $form['user[roles][0]']->setValue(User::ROLE_ADMIN);
        $this->client->submit($form);
        $this->assertStringContainsString('chewbacca@rebel.org', $this->client->getResponse()->getContent());

        $crawler = $this->client->request('GET', '/users');
        $this->assertEquals(2, $crawler->filter('td:contains("Administrateur")')->count());
    }

    public function testOnlyAdminCanDeleteAllUsers(): void
    {
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());
        $this->client->request('GET', '/users/2/delete');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));
        $this->client->request('GET', '/users/1/delete');
        $this->client->request('GET', '/users/2/delete');
        $this->assertStringContainsString('darth_vader', $this->client->getResponse()->getContent());
        $this->assertStringNotContainsString('han_solo', $this->client->getResponse()->getContent());
        $this->assertStringNotContainsString('luke_skywalker', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/users');
        $this->assertStringContainsString('Il n&#039;y a pas encore d&#039;utilisateur enregistré.', $this->client->getResponse()->getContent());
    }

    public function testAdminCannotDeleteSelf(): void
    {
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));
        $this->client->request('GET', '/users/3/delete');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }
}
