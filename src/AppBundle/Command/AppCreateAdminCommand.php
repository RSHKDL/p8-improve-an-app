<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AppCreateAdminCommand
 * @author ereshkidal
 */
class AppCreateAdminCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:create-admin')
            ->setDescription('Create an user with admin privileges')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
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

        /** @var UserPasswordEncoderInterface $passwordEncoder */
        $passwordEncoder = $this->getContainer()->get('security.password_encoder');

        $admin = new User();
        $admin->setUsername($username);
        $admin->setEmail($email);
        $admin->setPassword($passwordEncoder->encodePassword($admin, $password));
        $admin->setRoles([User::ROLE_ADMIN]);

        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->persist($admin);
        $em->flush();

        $style->success('Administrator successfully created');
    }

    /**
     * @param SymfonyStyle $style
     * @return string
     */
    private function askAndConfirmPassword(SymfonyStyle $style)
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
