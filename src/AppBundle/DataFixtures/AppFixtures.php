<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Task;
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
    public const DARTH_VADER = 'darth_vader';
    public const HAN_SOLO = 'han_solo';
    public const LUKE_SKYWALKER = 'luke_skywalker';

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
    public function load(ObjectManager $manager): void
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

        $hanSolo = $this->userHandler->createUserFromArray($userOneData);
        $lukeSkywalker = $this->userHandler->createUserFromArray($userTwoData);
        $this->userHandler->createUserFromArray($adminData);

        $taskOneData = new Task();
        $taskOneData->setTitle('Some title');
        $taskOneData->setContent('Some content');

        $taskTwoData = new Task();
        $taskTwoData->setTitle('Some other title');
        $taskTwoData->setContent('Some other content');

        $this->taskHandler->create($taskOneData, $hanSolo);
        $this->taskHandler->create($taskTwoData, $lukeSkywalker);

        $taskOneAnonymous = new Task();
        $taskOneAnonymous->setTitle('I am anonymous');
        $taskOneAnonymous->setContent('Some anonymous content');

        $taskTwoAnonymous = new Task();
        $taskTwoAnonymous->setTitle('I am anonymous too!');
        $taskTwoAnonymous->setContent('Some anonymous content');

        $this->taskHandler->create($taskOneAnonymous, null);
        $this->taskHandler->create($taskTwoAnonymous, null);
    }
}
