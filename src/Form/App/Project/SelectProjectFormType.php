<?php

namespace App\Form\App\Project;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectProjectFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['current_user'];

        $builder
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'required' => true,
                'multiple' => false,
                'autocomplete' => true,
                'label' => 'project.label',
                'choice_label' => 'name',
                'query_builder' => function (ProjectRepository $projectRepository) use ($user) {
                    return $projectRepository->getByUser($user);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'app',
            'current_user' => null,
        ]);

        $resolver->setRequired('current_user');
        $resolver->setAllowedTypes('current_user', User::class);
    }
}
