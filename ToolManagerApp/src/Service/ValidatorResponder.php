<?php


namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidatorResponder
{
    public function __construct(private ValidatorInterface $validator)
    {}

    public function validate(object $object): void
    {
        $errors = $this->validator->validate($object);
        if (count($errors) > 0) {
            throw new ValidationFailedException($object, $errors);
        }
    }
}
