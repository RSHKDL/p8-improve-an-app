<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class CreateAdminCommand
 * @author ereshkidal
 */
final class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:create-admin';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * CreateAdminCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param null $name
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        $name = null
    ) {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure(): void
    {
        $this->setDescription('Create an user with admin privileges');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('You are about to create an administrator');

        $username = $style->ask('Username?');
        $password = $this->askAndConfirmPassword($style);
        $email = $style->ask('Email?');

        $admin = new User();
        $admin->setUsername($username);
        $admin->setEmail($email);
        $admin->setPassword($this->passwordEncoder->encodePassword($admin, $password));
        $admin->setRoles(User::ROLE_ADMIN);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $style->success('Administrator successfully created');
    }

    /**
     * @param SymfonyStyle $style
     * @return string
     * @codeCoverageIgnore
     */
    private function askAndConfirmPassword(SymfonyStyle $style): string
    {
        $firstPassword = $style->askHidden('password?');
        $secondPassword = $style->askHidden('confirm your password');
        if ($firstPassword !== $secondPassword) {
            $style->warning('passwords does not match');
            $this->askAndConfirmPassword($style);
        }

        return $firstPassword;
    }
}
