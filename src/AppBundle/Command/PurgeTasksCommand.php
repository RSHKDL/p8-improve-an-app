<?php

namespace AppBundle\Command;

use AppBundle\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class PurgeTasksCommand
 * @author ereshkidal
 */
final class PurgeTasksCommand extends Command
{
    protected static $defaultName = 'app:purge-tasks';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TaskRepository
     */
    private $taskRepository;

    /**
     * PurgeTasksCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param TaskRepository $taskRepository
     * @param null $name
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TaskRepository $taskRepository,
        $name = null
    ) {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Purge all anonymous tasks')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Purge all tasks. Be careful with that.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('all')) {
            $this->purgeAll($io);

            return;
        }

        $this->purgeAnonymous($io);
    }

    /**
     * @param SymfonyStyle $io
     */
    private function purgeAnonymous(SymfonyStyle $io)
    {
        $tasks = $this->taskRepository->findAllAnonymous();
        $number = count($tasks);
        if(!$this->checkIfPurgeNeeded($number)) {
            $io->success('No anonymous tasks to purge');

            return;
        }

        $io->caution(sprintf('%s anonymous tasks will be deleted', $number));
        if (!$io->confirm('Are you sure ?')) {
            $io->error('Purge aborted');

            return;
        }

        foreach ($tasks as $task) {
            $this->entityManager->remove($task);
        }
        $this->entityManager->flush();
        $io->success('All anonymous tasks purged');
    }

    /**
     * @param SymfonyStyle $io
     */
    private function purgeAll(SymfonyStyle $io)
    {
        $tasks = $this->taskRepository->findAll();
        $number = count($tasks);
        if(!$this->checkIfPurgeNeeded($number)) {
            $io->success('No tasks to purge');

            return;
        }

        $io->warning(sprintf('You are about to delete all %s tasks.', $number));
        if (!$io->confirm('Are you sure ?', false)) {
            $io->error('Purge aborted');

            return;
        }

        foreach ($tasks as $task) {
            $this->entityManager->remove($task);
        }
        $this->entityManager->flush();
        $io->success('All tasks purged');
    }

    /**
     * @param int $number
     * @return bool
     */
    private function checkIfPurgeNeeded($number)
    {
        return !($number === 0);
    }
}
