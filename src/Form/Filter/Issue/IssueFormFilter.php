<?php

namespace App\Form\Filter\Issue;

use App\Entity\User;
use App\Form\AbstractFilterType;
use App\Model\Filter\IssueFilter;
use App\Repository\ProjectRepository;
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
