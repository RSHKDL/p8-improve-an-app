<?php

namespace AppBundle\DTO;

use AppBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserDTO
 * @author ereshkidal
 */
class UserDTO
{
    /**
     * @var string|null
     * @Assert\NotBlank(message="user.username.not_blank", groups={"registration", "edition"})
     * @CustomAssert\UniqueUser(message="user.username.unique", groups={"registration"})
     */
    public $username;

    /**
     * @var string|null
     * @Assert\NotBlank(message="user.email.not_blank", groups={"registration", "edition"})
     * @Assert\Email(message="user.email.format", groups={"registration", "edition"})
     * @CustomAssert\UniqueUser(message="user.email.unique", groups={"registration"})
     */
    public $email;

    /**
     * @var string|null
     * @Assert\NotBlank(message="user.password.mandatory", groups={"registration"})
     */
    public $plainPassword;

    /**
     * @var string|null
     */
    public $role;

    /**
     * @var array
     */
    public $roles;
}
