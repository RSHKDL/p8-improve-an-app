<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\AppFixtures;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class TaskControllerTest
 * @author ereshkidal
 */
class TaskControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @throws \Exception
     */
    public function setUp()
    {
        $this->client = static::createClient();
        $application = $this->getApplication();
        $this->createDatabaseAndSchema($application);
        $this->loadFixtures($application);
    }

    /**
     * @throws \Exception
     */
    public function tearDown()
    {
        $application = $this->getApplication();
        $this->dropDatabase($application);
    }

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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Créér une tâche")')->count());

        $link = $crawler->selectLink('Créér une tâche')->link();
        $crawler = $this->client->click($link);
        $this->assertContains('/tasks/create', $crawler->getUri());
        $this->assertContains(AppFixtures::HAN_SOLO, $this->client->getResponse()->getContent());
        $this->assertEquals(1, $crawler->filter('form')->count());

        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]']->setValue('Hello world');
        $form['task[content]']->setValue('This is a new task');
        $this->client->submit($form);
        $this->assertContains('La tâche a bien été ajoutée !', $this->client->getResponse()->getContent());
        $this->assertContains('Créée par '.AppFixtures::HAN_SOLO, $this->client->getResponse()->getContent());
    }

    public function testEditTaskByOwner()
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

    public function testEditOtherUserTask()
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $crawler = $this->client->request('GET', '/tasks');
        $link = $crawler->selectLink('Some other title')->link();
        $this->client->click($link);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testEditTaskWithAdminPrivileges()
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin(true));

        $crawler = $this->client->request('GET', '/tasks');
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

    public function testDeleteTask()
    {
        $this->client->followRedirects();
        $this->logIn($this->client, $this->fetchHanSoloOrAdmin());

        $this->client->request('GET', '/tasks/2/delete');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/tasks/1/delete');
        $this->assertContains('La tâche a bien été supprimée.', $this->client->getResponse()->getContent());
        $this->assertNotContains('Some title', $this->client->getResponse()->getContent());
    }

    /**
     * @param Client $client
     * @param User $user
     */
    protected function logIn(Client $client, User $user)
    {
        $session = $client->getContainer()->get('session');
        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    private function getContainer()
    {
        return $this->client->getContainer();
    }

    /**
     * @param bool $isAdmin
     * @return User
     */
    private function fetchHanSoloOrAdmin($isAdmin = false)
    {
        $username = $isAdmin ? AppFixtures::ADMIN : AppFixtures::HAN_SOLO;

        return $this
            ->getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);
    }

    /**
     * @return Application
     */
    private function getApplication()
    {
        $application = new Application($this->client->getKernel());
        $application->setAutoExit(false);

        return $application;
    }

    /**
     * @param Application $application
     * @throws \Exception
     */
    private function dropDatabase(Application $application)
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:drop',
            '--force' => true,
            '--no-interaction' => true,
            '--env' => 'test'
        ));

        $output = new NullOutput();
        $application->run($input, $output);
    }

    /**
     * @param Application $application
     * @throws \Exception
     */
    private function createDatabaseAndSchema(Application $application)
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:create',
            '--no-interaction' => true,
            '--env' => 'test'
        ));

        $output = new NullOutput();
        $application->run($input, $output);

        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:create',
            '--no-interaction' => true,
            '--env' => 'test'
        ));

        $output = new NullOutput();
        $application->run($input, $output);
    }

    /**
     * @param Application $application
     * @throws \Exception
     */
    private function loadFixtures(Application $application)
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:fixtures:load',
            '--no-interaction' => true,
            '--env' => 'test'
        ));

        $output = new NullOutput();
        $application->run($input, $output);
    }
}
