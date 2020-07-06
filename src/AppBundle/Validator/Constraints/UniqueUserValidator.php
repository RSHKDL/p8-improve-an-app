<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class UniqueUserValidator
 * @author ereshkidal
 */
class UniqueUserValidator extends ConstraintValidator
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param string $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        $existingUsername = $this->userRepository->findOneBy([
            'username' => $value
        ]);

        $existingEmail = $this->userRepository->findOneBy([
            'email' => $value
        ]);

        if (!$existingUsername && !$existingEmail) {
            return;
        }

        /** @var $constraint UniqueUser  */
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
