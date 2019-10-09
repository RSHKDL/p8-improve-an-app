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
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class BaseControllerTest
 * @author ereshkidal
 * @coversNothing
 */
class BaseControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

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

    public function testThisIsNotATest()
    {
        $this->assertTrue(true);
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

    /**
     * @param bool $isAdmin
     * @return User
     */
    protected function fetchHanSoloOrAdmin($isAdmin = false)
    {
        $username = $isAdmin ? AppFixtures::DARTH_VADER : AppFixtures::HAN_SOLO;

        return $this
            ->client
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
