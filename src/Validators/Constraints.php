<?php

namespace App\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class MinimalLenght extends Constraint
{
    public $message = 'The product must have the minimal properties required ("description", "price")';
}


final class MinimalLenghtValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (strlen($value) < 10){
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
