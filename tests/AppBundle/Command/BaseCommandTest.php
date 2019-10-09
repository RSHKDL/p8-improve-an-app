<?php

namespace Tests\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class BaseCommandTest extends KernelTestCase
{
    public function testThisIsNotATest()
    {
        $this->assertTrue(true);
    }

    /**
     * @return Application
     */
    protected function getApplication()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        return $application;
    }

    /**
     * @param Application $application
     * @throws \Exception
     */
    protected function createDatabaseAndSchema(Application $application)
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
    protected function loadFixtures(Application $application)
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:fixtures:load',
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
    protected function dropDatabase(Application $application)
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
}
