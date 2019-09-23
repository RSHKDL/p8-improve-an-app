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
        $this->checkIfPurgeNeeded($number, $io);

        $io->caution(sprintf('%s anonymous tasks will be deleted', $number));
        if (!$io->confirm('Are you sure ?')) {
            $io->error('Purge aborted');

            exit;
        }

        foreach ($tasks as $task) {
            $this->entityManager->remove($task);
        }
        $this->entityManager->flush();
        $io->success('Tasks purged');
    }

    /**
     * @param SymfonyStyle $io
     */
    private function purgeAll(SymfonyStyle $io)
    {
        $tasks = $this->taskRepository->findAll();
        $number = count($tasks);
        $this->checkIfPurgeNeeded($number, $io, true);
        $io->warning(sprintf('You are about to delete all %s tasks.', $number));
        if (!$io->confirm('Are you sure ?', false)) {
            $io->error('Purge aborted');

            exit;
        }

        foreach ($tasks as $task) {
            $this->entityManager->remove($task);
        }
        $this->entityManager->flush();
        $io->success('Tasks purged');
    }

    /**
     * @param int $number
     * @param SymfonyStyle $io
     * @param bool $all
     */
    private function checkIfPurgeNeeded($number, SymfonyStyle $io, $all = false)
    {
        if ($number === 0) {
            $io->success(sprintf('No %s tasks to purge', $all === false ? 'anonymous' : ''));

            exit;
        }
    }
}
