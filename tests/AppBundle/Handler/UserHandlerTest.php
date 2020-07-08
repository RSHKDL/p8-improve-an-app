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

    public function setUp(): void
    {
        $this->mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $this->mockPasswordEncoder = $this->createMock(UserPasswordEncoderInterface::class);
        $this->userHandler = new UserHandler($this->mockEntityManager, $this->mockPasswordEncoder);
    }

    /**
     * @dataProvider getCreateDtoData
     * @param UserDTO $dto
     * @param bool $mustFail
     */
    public function testCreateUserFromDto(UserDTO $dto, bool $mustFail = false): void
    {
        $this->mockEntityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class));
        $this->mockEntityManager->expects($this->atLeastOnce())->method('flush');

        $user = $this->userHandler->createUserFromDTO($dto);
        if (!$mustFail) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertIsString($user->getUsername());
            $this->assertIsString($user->getEmail());
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider getCreateArrayData
     * @param array $data
     * @param bool $mustFail
     */
    public function testCreateUserFromArray(array $data, $mustFail = false): void
    {
        $this->mockEntityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class));
        $this->mockEntityManager->expects($this->atLeastOnce())->method('flush');

        $user = $this->userHandler->createUserFromArray($data);
        if (!$mustFail) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertIsString($user->getUsername());
            $this->assertIsString($user->getEmail());
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider getUpdateData
     * @param User $user
     * @param UserDTO $dto
     */
    public function testUpdate(User $user, UserDTO $dto): void
    {
        $this->mockEntityManager->expects($this->atLeastOnce())->method('flush');

        $user = $this->userHandler->update($user, $dto);
        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * @dataProvider getDeleteData
     * @param User $user
     */
    public function testDelete(User $user): void
    {
        $this->mockEntityManager->expects($this->once())->method('remove')->with($this->isInstanceOf(User::class));
        $this->mockEntityManager->expects($this->atLeastOnce())->method('flush');

        $this->userHandler->delete($user);
    }

    public function getCreateDtoData(): array
    {
        return [
            '#1 Valid data' => [$this->provideDtoData()],
            '#2 Missing email' => [$this->provideDtoData(true), true]
        ];
    }

    public function getCreateArrayData(): array
    {
        return [
            '#1 Valid data' => [$this->provideArrayData()],
            '#2 Missing email' => [$this->provideArrayData(true), true]
        ];
    }

    public function getUpdateData(): array
    {
        return [
            '#1 Valid instance' => [$this->provideUser(), $this->provideUserDto()]
        ];
    }

    public function getDeleteData(): array
    {
        return [
            '#1 Valid instance' => [$this->provideUser()]
        ];
    }

    private function provideDtoData(bool $incomplete = false): UserDTO
    {
        $dto = new UserDTO();
        $dto->username = 'john';
        $dto->email = $incomplete === true ? null : 'john@doe.com';
        $dto->plainPassword = '1234';

        return $dto;
    }

    private function provideArrayData(bool $incomplete = false): array
    {
        return [
            'username' => 'john',
            'password' => '1234',
            'email' => $incomplete === true ? null : 'john@doe.com',
            'roles' => null
        ];
    }

    private function provideUser(): User
    {
        return new User();
    }

    private function provideUserDto(): UserDTO
    {
        $dto = new UserDTO();
        $dto->plainPassword = '1234';

        return $dto;
    }
}
