<?php

namespace App\Form\Admin\Project;

use App\Entity\User;
use App\Message\Command\Admin\Project\AbstractProjectDTO;
use App\Message\Command\Admin\Project\EditProject;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class ProjectFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jiraKey', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Length(max: 255),
                ],
                'disabled' => $options['editable'],
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'multiple' => true,
                'required' => false,
                'autocomplete' => true,
                'attr' => [
                    'data-controller' => 'form-control select2',
                ],
            ])
        ;

        if (is_a($builder->getData(), EditProject::class)) {
            $builder
                ->add('assignableRolesIds', ChoiceType::class, [
                    'choices' => $options['roles'],
                    'multiple' => true,
                    'required' => false,
                    'autocomplete' => true,
                    'attr' => [
                        'data-controller' => 'form-control select2',
                    ],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AbstractProjectDTO::class,
            'translation_domain' => 'app',
            'label_format' => 'project.%name%.label',
            'editable' => false,
            'csrf_protection' => false,
            'roles' => [],
        ]);
    }
}
