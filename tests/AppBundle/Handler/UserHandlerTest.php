<?php

namespace Tests\AppBundle\Handler;

use AppBundle\DTO\UserDTO;
use AppBundle\Entity\User;
use AppBundle\Handler\UserHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserHandlerTest
 * @author ereshkidal
 * @covers \AppBundle\Handler\UserHandler
 */
class UserHandlerTest extends TestCase
{
    /**
     * @var UserHandler
     */
    private $userHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEntityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPasswordEncoder;

    public function setUp()
    {
        $this->mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $this->mockPasswordEncoder = $this->createMock(UserPasswordEncoderInterface::class);
        $this->userHandler = new UserHandler($this->mockEntityManager, $this->mockPasswordEncoder);
    }

    /**
     * @dataProvider getCreateData
     * @param array $data
     * @param bool $mustFail
     */
    public function testCreateUserFromArray(array $data, $mustFail = false)
    {
        $this->mockEntityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class));
        $this->mockEntityManager->expects($this->atLeastOnce())->method('flush');

        $user = $this->userHandler->createUserFromArray($data);
        if (!$mustFail) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertInternalType('string', $user->getUsername());
            $this->assertInternalType('string', $user->getEmail());
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider getUpdateData
     * @param User $user
     * @param UserDTO $dto
     */
    public function testUpdate(User $user, UserDTO $dto)
    {
        $this->mockEntityManager->expects($this->atLeastOnce())->method('flush');

        $user = $this->userHandler->update($user, $dto);
        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * @dataProvider getDeleteData
     * @param User $user
     */
    public function testDelete(User $user)
    {
        $this->mockEntityManager->expects($this->once())->method('remove')->with($this->isInstanceOf(User::class));
        $this->mockEntityManager->expects($this->atLeastOnce())->method('flush');

        $this->userHandler->delete($user);
    }

    /**
     * @return array
     */
    public function getCreateData()
    {
        return [
            '#1 Valid data' => [$this->provideData()],
            '#2 Missing email' => [$this->provideData(true), true]
        ];
    }

    /**
     * @return array
     */
    public function getUpdateData()
    {
        return [
            '#1 Valid instance' => [$this->provideUser(), $this->provideUserDto()]
        ];
    }

    /**
     * @return array
     */
    public function getDeleteData()
    {
        return [
            '#1 Valid instance' => [$this->provideUser()]
        ];
    }

    /**
     * @param bool $incomplete
     * @return array
     */
    private function provideData($incomplete = false)
    {
        return [
            'username' => 'john',
            'password' => '1234',
            'email' => $incomplete === true ? null : 'john@doe.com',
            'roles' => null
        ];
    }

    /**
     * @return User
     */
    private function provideUser(): User
    {
        return new User();
    }

    private function provideUserDto(): UserDTO
    {
        return new UserDTO();
    }
}
