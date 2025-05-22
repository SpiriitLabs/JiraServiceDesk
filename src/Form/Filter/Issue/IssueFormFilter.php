<?php

namespace App\Form\Filter\Issue;

use App\Entity\Project;
use App\Entity\User;
use App\Form\AbstractFilterType;
use App\Model\Filter\IssueFilter;
use App\Repository\ProjectRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IssueFormFilter extends AbstractFilterType
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['current_user'];

        $builder
            ->add('query', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'filter.query.label',
                ],
            ])
            ->add('projects', EntityType::class, [
                'class' => Project::class,
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'label' => false,
                'attr' => [
                    'placeholder' => 'project.label',
                ],
                'choice_label' => 'name',
                'query_builder' => function (ProjectRepository $projectRepository) use ($user) {
                    return $projectRepository->getByUser($user);
                },
                'data' => $this->projectRepository->getByUser($user)
                    ->getQuery()
                    ->getResult(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => IssueFilter::class,
            'current_user' => null,
        ]);

        $resolver->setRequired('current_user');
    }
}
