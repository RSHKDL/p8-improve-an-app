<?php


namespace AppBundle\Handler;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
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
     * TaskHandler constructor.
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
     * @param FormInterface $form
     * @return User
     */
    public function create(FormInterface $form): User
    {
        $user = $form->getData();
        $user->setPassword($this->passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData()));
        $user->setRoles([User::ROLE_USER]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param User $user
     * @return User
     */
    public function update(User $user): User
    {
        if (null !== $user->getPassword()) {
            $password = $this->passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
        }
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
        $user->setRoles([$data['roles'] ?? User::ROLE_USER]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
