<?php


namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorResponder
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function validate(object $object): ?JsonResponse
    {
        $errors = $this->validator->validate($object);
        $messages = [];
        $details = [];

        if (count($errors) > 0) {
            foreach ($errors as $error) {

                $field = $error->getPropertyPath();

                if (!isset($details[$field])) {
                    $details[$field] = $error->getMessage();
                }else{
                    if (!is_array($details[$field])) {
                        $details[$field] = [$details[$field]];
                    }
                    $details[$field][] = $error->getMessage();
                }
            }

            return new JsonResponse([
                'errors' => "Validation failed",
                'details' => $details,
            ], 422);
        }

        return null;
    }
}
