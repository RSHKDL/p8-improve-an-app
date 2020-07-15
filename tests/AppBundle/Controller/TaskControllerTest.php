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
    public function testIfRedirectedWhenNotAuthenticated(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/tasks');

        $this->assertStringContainsString('/login', $crawler->getUri());
    }

    public function testCreateTask(): void
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $crawler = $this->client->request('GET', '/tasks');
        $this->assertStringContainsString('/tasks', $crawler->getUri());
        $this->assertStringContainsString(AppFixtures::HAN_SOLO, $this->client->getResponse()->getContent());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Ajouter une tâche")')->count());

        $link = $crawler->selectLink('Ajouter une tâche')->link();
        $crawler = $this->client->click($link);
        $this->assertStringContainsString('/tasks/create', $crawler->getUri());
        $this->assertStringContainsString('Ajouter une tâche', $this->client->getResponse()->getContent());
        $this->assertEquals(1, $crawler->filter('form')->count());

        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]']->setValue('Hello world');
        $form['task[content]']->setValue('This is a new task');
        $this->client->submit($form);
        $this->assertStringContainsString('La tâche a bien été ajoutée !', $this->client->getResponse()->getContent());
        $this->assertStringContainsString('Hello world', $this->client->getResponse()->getContent());
    }

    public function testEditTask(): void
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $crawler = $this->client->request('GET', '/tasks');
        $link = $crawler->selectLink('Some title')->link();
        $crawler = $this->client->click($link);
        $this->assertStringContainsString('/tasks/1/edit', $crawler->getUri());
        $this->assertEquals(1, $crawler->filter('form')->count());

        $form = $crawler->selectButton('Éditer')->form();
        $form['task[content]']->setValue('I have edited the content');
        $this->client->submit($form);
        $this->assertStringContainsString('La tâche a bien été modifiée.', $this->client->getResponse()->getContent());
        $this->assertStringContainsString('I have edited the content', $this->client->getResponse()->getContent());
    }

    public function testUserCannotEditNotOwnedTask(): void
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $crawler = $this->client->request('GET', '/tasks/public');
        $link = $crawler->selectLink('Some other title')->link();
        $this->client->click($link);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminCanEditAnyTask(): void
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));

        $crawler = $this->client->request('GET', '/tasks/public');
        $link = $crawler->selectLink('Some title')->link();
        $crawler = $this->client->click($link);
        $this->assertStringContainsString('/tasks/1/edit', $crawler->getUri());
        $this->assertEquals(1, $crawler->filter('form')->count());

        $form = $crawler->selectButton('Éditer')->form();
        $form['task[content]']->setValue('I am an admin and I can edit the content');
        $this->client->submit($form);
        $this->assertStringContainsString('La tâche a bien été modifiée.', $this->client->getResponse()->getContent());
        $this->assertStringContainsString('I am an admin and I can edit the content', $this->client->getResponse()->getContent());
    }

    /**
     * @todo User should only be able to toggle owned or attributed task
     */
    public function testToggleTask(): void
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $this->client->request('GET', '/tasks/1/toggle');
        $this->assertStringContainsString('La tâche Some title a bien été marquée comme faite.', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/tasks/archived');
        $this->assertStringContainsString('Some title', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/tasks/public/archived');
        $this->assertStringContainsString('Some title', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/tasks/1/toggle');
        $this->assertStringContainsString('La tâche Some title a bien été marquée comme non terminée.', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/tasks/2/toggle');
        $this->assertStringContainsString('La tâche Some other title a bien été marquée comme faite.', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/tasks/2/toggle');
        $this->assertStringContainsString('La tâche Some other title a bien été marquée comme non terminée.', $this->client->getResponse()->getContent());
    }

    public function testUserCanOnlyDeleteOwnedTask(): void
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $this->client->request('GET', '/tasks/2/delete');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/tasks/1/delete');
        $this->assertStringContainsString('La tâche a bien été supprimée.', $this->client->getResponse()->getContent());
        $this->assertStringNotContainsString('Some title', $this->client->getResponse()->getContent());
    }

    public function testAdminCanDeleteAnyTask(): void
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));

        $this->client->request('GET', '/tasks/1/delete');
        $this->assertStringContainsString('La tâche a bien été supprimée.', $this->client->getResponse()->getContent());
        $this->assertStringNotContainsString('Some title', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/tasks/2/delete');
        $this->assertStringContainsString('La tâche a bien été supprimée.', $this->client->getResponse()->getContent());
        $this->assertStringNotContainsString('Some other title', $this->client->getResponse()->getContent());
    }

    public function testFilterPublicTask(): void
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $this->client->request('GET', '/tasks/public');
        $this->assertStringContainsString('Some title', $this->client->getResponse()->getContent());
        $this->assertStringContainsString('Some other title', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/tasks/public?filter=other');
        $this->assertStringContainsString('Some other title', $this->client->getResponse()->getContent());
        $this->assertStringNotContainsString('Some title', $this->client->getResponse()->getContent());
    }
}
