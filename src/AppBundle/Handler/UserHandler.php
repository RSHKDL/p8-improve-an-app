<?php

namespace AppBundle\Handler;

use AppBundle\DTO\UserDTO;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserHandler
 * @author ereshkidal
 */
final class UserHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * UserHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @todo Send a mail to the newly created user with his credentials
     * @param UserDTO $dto
     * @return User
     */
    public function createUserFromDTO(UserDTO $dto): User
    {
        $user = new User();
        $user->setUsername($dto->username);
        $user->setEmail($dto->email);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $dto->plainPassword));
        $user->setRoles(null === $dto->role ? User::ROLE_USER : $dto->role);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param User $user
     * @param UserDTO $dto
     * @return User
     */
    public function update(User $user, UserDTO $dto): User
    {
        $user->setUsername($dto->username);
        $user->setEmail($dto->email);
        if (null !== $dto->plainPassword) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $dto->plainPassword));
        }
        $user->setRoles($dto->roles);

        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param User $user
     */
    public function delete(User $user)
    {
        $tasks = $user->getTasks();
        foreach ($tasks as $task) {
            $task->setAuthor(null);
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    /**
     * @param array $data
     * @return User
     */
    public function createUserFromArray(array $data): User
    {
        $user = new User();
        $user->setUsername($data['username']);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $data['password']));
        $user->setEmail($data['email']);
        $user->setRoles($data['roles'] ?? User::ROLE_USER);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param User $user
     * @return UserDTO
     */
    public function createUserDtoFromUser(User $user): UserDTO
    {
        $dto = new UserDTO();
        $dto->username = $user->getUsername();
        $dto->email = $user->getEmail();
        $dto->roles = $user->getRoles();

        return $dto;
    }
}
