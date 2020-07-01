<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\AppFixtures;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskControllerTest
 * @author ereshkidal
 */
class TaskControllerTest extends BaseControllerTest
{
    public function testIfRedirectedWhenNotAuthenticated()
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/tasks');

        $this->assertContains('/login', $crawler->getUri());
    }

    public function testCreateTask()
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $crawler = $this->client->request('GET', '/tasks');
        $this->assertContains('/tasks', $crawler->getUri());
        $this->assertContains(AppFixtures::HAN_SOLO, $this->client->getResponse()->getContent());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Ajouter une tâche")')->count());

        $link = $crawler->selectLink('Ajouter une tâche')->link();
        $crawler = $this->client->click($link);
        $this->assertContains('/tasks/create', $crawler->getUri());
        $this->assertContains('Ajouter une tâche', $this->client->getResponse()->getContent());
        $this->assertEquals(1, $crawler->filter('form')->count());

        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]']->setValue('Hello world');
        $form['task[content]']->setValue('This is a new task');
        $this->client->submit($form);
        $this->assertContains('La tâche a bien été ajoutée !', $this->client->getResponse()->getContent());
        $this->assertContains('Hello world', $this->client->getResponse()->getContent());
    }

    public function testEditTask()
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $crawler = $this->client->request('GET', '/tasks');
        $link = $crawler->selectLink('Some title')->link();
        $crawler = $this->client->click($link);
        $this->assertContains('/tasks/1/edit', $crawler->getUri());
        $this->assertEquals(1, $crawler->filter('form')->count());

        $form = $crawler->selectButton('Éditer')->form();
        $form['task[content]']->setValue('I have edited the content');
        $this->client->submit($form);
        $this->assertContains('La tâche a bien été modifiée.', $this->client->getResponse()->getContent());
        $this->assertContains('I have edited the content', $this->client->getResponse()->getContent());
    }

    public function testUserCannotEditNotOwnedTask()
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $crawler = $this->client->request('GET', '/tasks/public');
        $link = $crawler->selectLink('Some other title')->link();
        $this->client->click($link);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminCanEditAnyTask()
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));

        $crawler = $this->client->request('GET', '/tasks/public');
        $link = $crawler->selectLink('Some title')->link();
        $crawler = $this->client->click($link);
        $this->assertContains('/tasks/1/edit', $crawler->getUri());
        $this->assertEquals(1, $crawler->filter('form')->count());

        $form = $crawler->selectButton('Éditer')->form();
        $form['task[content]']->setValue('I am an admin and I can edit the content');
        $this->client->submit($form);
        $this->assertContains('La tâche a bien été modifiée.', $this->client->getResponse()->getContent());
        $this->assertContains('I am an admin and I can edit the content', $this->client->getResponse()->getContent());
    }

    /**
     * @todo User should only be able to toggle owned or attributed task
     */
    public function testToggleTask()
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $this->client->request('GET', '/tasks/1/toggle');
        $this->assertContains('La tâche Some title a bien été marquée comme faite.', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/tasks/1/toggle');
        $this->assertContains('La tâche Some title a bien été marquée comme non terminée.', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/tasks/2/toggle');
        $this->assertContains('La tâche Some other title a bien été marquée comme faite.', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/tasks/2/toggle');
        $this->assertContains('La tâche Some other title a bien été marquée comme non terminée.', $this->client->getResponse()->getContent());
    }

    public function testUserCanOnlyDeleteOwnedTask()
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $this->client->request('GET', '/tasks/2/delete');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/tasks/1/delete');
        $this->assertContains('La tâche a bien été supprimée.', $this->client->getResponse()->getContent());
        $this->assertNotContains('Some title', $this->client->getResponse()->getContent());
    }

    public function testAdminCanDeleteAnyTask()
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));

        $this->client->request('GET', '/tasks/1/delete');
        $this->assertContains('La tâche a bien été supprimée.', $this->client->getResponse()->getContent());
        $this->assertNotContains('Some title', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/tasks/2/delete');
        $this->assertContains('La tâche a bien été supprimée.', $this->client->getResponse()->getContent());
        $this->assertNotContains('Some other title', $this->client->getResponse()->getContent());
    }
}
