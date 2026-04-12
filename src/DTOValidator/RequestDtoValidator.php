<?php

declare(strict_types=1);

namespace App\DTOValidator;

use App\Exception\ValidateException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestDtoValidator
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function validate(object $dto): void
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) === 0) {
            return;
        }

        $messages = [];
        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()][] = $error->getMessage();
        }

        throw new ValidateException($messages);
    }
}
