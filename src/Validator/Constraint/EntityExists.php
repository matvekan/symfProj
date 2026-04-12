<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class EntityExists extends Constraint
{
    public string $entity;
    public string $message = 'Entity {{entity}} with ID {{id}} not exists';

    public function __construct(string $entity, mixed $options = null, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct($options, $groups, $payload);
        $this->entity = $entity;
    }

    public function validatedBy(): string
    {
        return EntityExistsValidator::class;
    }
}
