<?php

declare(strict_types=1);

namespace App\Validator\User;

use Symfony\Component\Validator\Constraint;

#[\Attribute()]
class UniqueEmail extends Constraint
{
    public string $message = 'user.unique_email.message';

    public function __construct(
        ?string $message = null,
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
    }
}
