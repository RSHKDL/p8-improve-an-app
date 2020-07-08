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
    public function setUp(): void
    {
        $application = $this->getApplication();
        $this->createDatabaseAndSchema($application);
        $this->loadFixtures($application);
    }

    /**
     * @throws \Exception
     */
    public function tearDown(): void
    {
        $application = $this->getApplication();
        $this->dropDatabase($application);
    }


    public function testPurgeAllTasks(): void
    {
        $command = $this->getApplication()->find('app:purge-tasks');
        $commandTester = new CommandTester($command);

        // test abort
        $commandTester->setInputs(['No']);
        $commandTester->execute([
            'command' => $command->getName(),
            '--all' => true
        ]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Purge aborted', $output);

        // test proceed
        $commandTester->setInputs(['Yes']);
        $commandTester->execute([
            'command' => $command->getName(),
            '--all' => true
        ]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('All tasks purged', $output);
    }

    public function testPurgeAnonymousTasks(): void
    {
        $command = $this->getApplication()->find('app:purge-tasks');
        $commandTester = new CommandTester($command);

        // test abort
        $commandTester->setInputs(['No']);
        $commandTester->execute(['command' => $command->getName()]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Purge aborted', $output);

        // test proceed
        $commandTester->setInputs(['Yes']);
        $commandTester->execute(['command' => $command->getName()]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('All anonymous tasks purged', $output);

        // test proceed again
        $commandTester->setInputs(['Yes']);
        $commandTester->execute(['command' => $command->getName()]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('No anonymous tasks to purge', $output);
    }

    public function testNothingNeedToBePurged(): void
    {
        $command = $this->getApplication()->find('app:purge-tasks');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['Yes']);
        $commandTester->execute([
            'command' => $command->getName(),
            '--all' => true
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('All tasks purged', $output);

        $commandTester->setInputs(['Yes']);
        $commandTester->execute([
            'command' => $command->getName(),
            '--all' => true
        ]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('No tasks to purge', $output);
    }
}
