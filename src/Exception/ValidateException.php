<?php

declare(strict_types=1);

namespace App\Exception;

final class ValidateException extends \RuntimeException
{
    /**
     * @param array<string, array<int, string>> $errors
     */
    public function __construct(private array $errors)
    {
        parent::__construct('Invalid arguments', 422);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
