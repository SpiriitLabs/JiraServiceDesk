<?php

declare(strict_types=1);

namespace App\Form\Admin\User;

use App\Message\Command\User\ChangePasswordUser;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordRequirements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class UserChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPlainPassword', PasswordType::class, [
                'label' => 'user.password.current',
                'required' => true,
            ])
            ->add('newPlainPassword', PasswordType::class, [
                'label' => 'user.password.new',
                'required' => true,
                'constraints' => [
                    new NotCompromisedPassword(),
                    new PasswordRequirements(
                        minLength: 8,
                        requireCaseDiff: true,
                        requireNumbers: true,
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangePasswordUser::class,
            'translation_domain' => 'app',
            'label_format' => 'user.%name%.label',
        ]);
    }
}
