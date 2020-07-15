<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueUser
 * @author ereshkidal
 * @Annotation
 */
class UniqueUser extends Constraint
{
    public $message = '{{ value }} is already used!';
}
