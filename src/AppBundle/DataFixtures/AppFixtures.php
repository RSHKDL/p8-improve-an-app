<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use AppBundle\Handler\UserHandler;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class AppFixtures
 * @author ereshkidal
 */
class AppFixtures extends Fixture
{
    const ADMIN_NAME = 'admin';
    const USER_NAME = 'hansolo';

    /**
     * @var UserHandler
     */
    private $userHandler;

    /**
     * AppFixtures constructor.
     * @param UserHandler $userHandler
     */
    public function __construct(UserHandler $userHandler)
    {
        $this->userHandler = $userHandler;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $userData = [
            'username' => self::USER_NAME,
            'password' => '1234',
            'email' => self::USER_NAME.'@mail.com',
            'roles' => User::ROLE_USER
        ];
        $adminData = [
            'username' => self::ADMIN_NAME,
            'password' => '1234',
            'email' => self::ADMIN_NAME.'@mail.com',
            'roles' => User::ROLE_ADMIN
        ];
        $this->userHandler->create($userData);
        $this->userHandler->create($adminData);
    }
}
