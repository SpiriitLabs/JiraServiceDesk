<?php

namespace App\Form\Admin\User;

use App\Entity\UserNotificationPreferences;
use App\Form\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserNotificationPreferencesType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('issueCreated', SwitchType::class, [
                'required' => false,
                'attr' => [
                    'data-notification-profil-target' => 'issueCreated',
                    'data-action' => 'notification-profil#notification',
                ],
            ])
            ->add('issueUpdated', SwitchType::class, [
                'required' => false,
                'attr' => [
                    'data-notification-profil-target' => 'issueUpdated',
                    'data-action' => 'notification-profil#notification',
                ],
            ])
            ->add('commentCreated', SwitchType::class, [
                'required' => false,
                'attr' => [
                    'data-notification-profil-target' => 'commentCreated',
                    'data-action' => 'notification-profil#notification notification-profil#commentCreatedOrUpdated',
                ],
            ])
            ->add('commentUpdated', SwitchType::class, [
                'required' => false,
                'attr' => [
                    'data-notification-profil-target' => 'commentUpdated',
                    'data-action' => 'notification-profil#notification notification-profil#commentCreatedOrUpdated',
                ],
            ])
            ->add('commentOnlyOnTag', SwitchType::class, [
                'required' => false,
                'attr' => [
                    'data-notification-profil-target' => 'commentTagOnly',
                    'data-action' => 'notification-profil#notification notification-profil#commentTagOnly',
                ],
            ])
        ;

        if ($options['has_global'] == false) {
            $builder
                ->add('global', HiddenType::class, [
                    'empty_data' => true,
                    'attr' => [
                        'data-notification-profil-target' => 'all',
                        'data-action' => 'notification-profil#toggleAll',
                    ],
                ])
            ;
        } else {
            $builder
                ->add('global', SwitchType::class, [
                    'required' => false,
                    'attr' => [
                        'data-notification-profil-target' => 'all',
                        'data-action' => 'notification-profil#toggleAll',
                    ],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserNotificationPreferences::class,
            'translation_domain' => 'app',
            'label_format' => 'user.notification_preferences.%name%.label',
            'has_global' => true,
        ]);
    }

}
