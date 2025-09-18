<?php

namespace App\Validator\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function validate(
        mixed $value,
        Constraint $constraint,
    ): void {
        if (! $constraint instanceof UniqueEmail) {
            throw new UnexpectedTypeException($constraint, UniqueEmail::class);
        }

        if ($value === null) {
            return;
        }

        if (! \is_string($value)) {
            throw new \UnexpectedValueException($value, 'string');
        }

        if ($this->userRepository->findOneBy([
            'email' => $value,
        ]) instanceof User) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
