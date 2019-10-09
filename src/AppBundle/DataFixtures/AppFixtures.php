<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use AppBundle\Handler\TaskHandler;
use AppBundle\Handler\UserHandler;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class AppFixtures
 * @author ereshkidal
 */
class AppFixtures extends Fixture
{
    const DARTH_VADER = 'darth_vader';
    const HAN_SOLO = 'han_solo';
    const LUKE_SKYWALKER = 'luke_skywalker';

    /**
     * @var UserHandler
     */
    private $userHandler;

    /**
     * @var TaskHandler
     */
    private $taskHandler;

    /**
     * AppFixtures constructor.
     * @param UserHandler $userHandler
     * @param TaskHandler $taskHandler
     */
    public function __construct(
        UserHandler $userHandler,
        TaskHandler $taskHandler
    ) {
        $this->userHandler = $userHandler;
        $this->taskHandler = $taskHandler;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $userOneData = [
            'username' => self::HAN_SOLO,
            'password' => '1234',
            'email' => self::HAN_SOLO.'@rebel.com',
            'roles' => User::ROLE_USER
        ];
        $userTwoData = [
            'username' => self::LUKE_SKYWALKER,
            'password' => '1234',
            'email' => self::LUKE_SKYWALKER.'@rebel.com',
            'roles' => User::ROLE_USER
        ];
        $adminData = [
            'username' => self::DARTH_VADER,
            'password' => '1234',
            'email' => self::DARTH_VADER.'@empire.com',
            'roles' => User::ROLE_ADMIN
        ];
        $hanSolo = $this->userHandler->create($userOneData);
        $lukeSkywalker = $this->userHandler->create($userTwoData);
        $this->userHandler->create($adminData);

        $taskOneData = [
            'title' => 'Some title',
            'content' => 'Some content'
        ];
        $taskTwoData = [
            'title' => 'Some other title',
            'content' => 'Some other content'
        ];
        $this->taskHandler->create($taskOneData, $hanSolo);
        $this->taskHandler->create($taskTwoData, $lukeSkywalker);
    }
}
