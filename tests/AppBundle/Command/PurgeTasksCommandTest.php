<?php

namespace Tests\AppBundle\Command;

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class PurgeTasksCommandTest
 * @author ereshkidal
 * @covers \AppBundle\Command\PurgeTasksCommand
 */
class PurgeTasksCommandTest extends BaseCommandTest
{
    /**
     * @throws \Exception
     */
    public function setUp()
    {
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


    public function testPurgeAllTasks()
    {
        $command = $this->getApplication()->find('app:purge-tasks');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['Yes']);
        $commandTester->execute([
            'command' => $command->getName(),
            '--all' => true
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('All tasks purged', $output);
    }

    public function testPurgeAnonymousTasks()
    {
        $this->markTestSkipped('no anonymous tasks in fixtures');

        $command = $this->getApplication()->find('app:purge-tasks');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['Yes']);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('All anonymous tasks purged', $output);
    }

    public function testNothingNeedToBePurged()
    {
        $command = $this->getApplication()->find('app:purge-tasks');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['Yes']);
        $commandTester->execute([
            'command' => $command->getName(),
            '--all' => true
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('All tasks purged', $output);

        $commandTester->setInputs(['Yes']);
        $commandTester->execute([
            'command' => $command->getName(),
            '--all' => true
        ]);
        $output = $commandTester->getDisplay();
        $this->assertContains('No tasks to purge', $output);
    }
}
