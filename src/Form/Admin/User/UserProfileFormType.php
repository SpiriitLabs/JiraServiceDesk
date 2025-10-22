<?php

declare(strict_types=1);

namespace App\Form\Admin\User;

use Symfony\Component\Form\FormBuilderInterface;

class UserProfileFormType extends AbstractUserFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
    }
}
