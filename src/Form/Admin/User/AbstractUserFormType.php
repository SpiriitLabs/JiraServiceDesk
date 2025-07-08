<?php

namespace App\Form\Admin\User;

use App\Entity\Project;
use App\Enum\User\Locale;
use App\Enum\User\Theme;
use App\Form\Type\SwitchType;
use App\Message\Command\User\AbstractUserDTO;
use App\Message\Command\User\EditUser;
use App\Repository\ProjectRepository;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordRequirements;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

abstract class AbstractUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => \in_array('create', $options['validation_groups'] ?? [], true),
                'disabled' => \in_array('create', $options['validation_groups'] ?? [], true) == false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('firstName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'user.password.label',
                'required' => \in_array('create', $options['validation_groups'] ?? [], true),
                'constraints' => [
                    new NotBlank(groups: ['create']),
                    new NotCompromisedPassword(),
                    new PasswordRequirements(
                        minLength: 8,
                        requireCaseDiff: true,
                        requireNumbers: true,
                    ),
                ],
            ])
            ->add('preferedLocale', EnumType::class, [
                'required' => true,
                'class' => Locale::class,
                'constraints' => [
                    new NotBlank(),
                ],
                'choice_label' => fn (Locale $locale) => $locale->label(),
            ])
            ->add('preferedTheme', EnumType::class, [
                'required' => true,
                'class' => Theme::class,
                'constraints' => [
                    new NotBlank(),
                ],
                'choice_label' => fn (Theme $theme) => $theme->label(),
            ])
            ->add('preferenceNotification', SwitchType::class, [
                'required' => false,
            ])
            ->add('preferenceNotificationIssueCreated', SwitchType::class, [
                'required' => false,
            ])
            ->add('preferenceNotificationIssueUpdated', SwitchType::class, [
                'required' => false,
            ])
            ->add('preferenceNotificationCommentCreated', SwitchType::class, [
                'required' => false,
            ])
            ->add('preferenceNotificationCommentUpdated', SwitchType::class, [
                'required' => false,
            ])
            ->add('preferenceNotificationCommentOnlyOnTag', SwitchType::class, [
                'required' => false,
            ])
        ;

        if (is_a($builder->getData(), EditUser::class)) {
            $builder
                ->add('defaultProject', EntityType::class, [
                    'required' => false,
                    'class' => Project::class,
                    'choice_label' => fn (Project $project) => $project->name,
                    'query_builder' => function (ProjectRepository $projectRepository) use ($builder) {
                        return $projectRepository->getByUser($builder->getData()->user);
                    },
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AbstractUserDTO::class,
            'translation_domain' => 'app',
            'label_format' => 'user.%name%.label',
        ]);
    }
}
