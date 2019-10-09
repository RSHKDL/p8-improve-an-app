<?php

namespace Tests\AppBundle\Command;

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CreateAdminCommandTest
 * @author ereshkidal
 * @covers \AppBundle\Command\CreateAdminCommand
 */
class CreateAdminCommandTest extends BaseCommandTest
{
    /**
     * @throws \Exception
     */
    public function setUp()
    {
        $application = $this->getApplication();
        $this->createDatabaseAndSchema($application);
    }

    /**
     * @throws \Exception
     */
    public function tearDown()
    {
        $application = $this->getApplication();
        $this->dropDatabase($application);
    }

    public function testExecute()
    {
        $command = $this->getApplication()->find('app:create-admin');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs([
            'administrator',
            'password',
            'password',
            'administrator@todoandco.com'
        ]);

        $commandTester->execute([
            'command' => $command->getName()
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Administrator successfully created', $output);
    }
}
