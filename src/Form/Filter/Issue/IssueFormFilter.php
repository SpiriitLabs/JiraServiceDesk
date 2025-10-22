<?php

declare(strict_types=1);

namespace App\Form\Filter\Issue;

use App\Enum\User\Role;
use App\Form\AbstractFilterType;
use App\Form\Type\SwitchType;
use App\Model\Filter\IssueFilter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IssueFormFilter extends AbstractFilterType
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'filter.query.label',
                ],
            ])
            ->add('hasResolvedMasked', SwitchType::class, [
                'required' => false,
                'label' => 'issue.filter.hasResolvedMasked.label',
                'data' => true,
            ])
        ;

        if ($options['statuses'] !== []) {
            $builder
                ->add('statusesIds', ChoiceType::class, [
                    'choices' => $options['statuses'],
                    'label' => false,
                    'multiple' => true,
                    'required' => false,
                    'autocomplete' => true,
                    'attr' => [
                        'placeholder' => 'issue.status.label',
                    ],
                ])
            ;
        }

        if ($this->security->isGranted(Role::ROLE_APP_CAN_ASSIGNEE) && $options['assignees'] !== []) {
            $builder
                ->add('assigneeIds', ChoiceType::class, [
                    'choices' => $options['assignees'],
                    'label' => false,
                    'multiple' => true,
                    'required' => false,
                    'autocomplete' => true,
                    'attr' => [
                        'data-controller' => 'form-control select2',
                        'placeholder' => 'issue.assignee.label',
                    ],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => IssueFilter::class,
            'current_user' => null,
            'statuses' => [],
            'assignees' => [],
        ]);

        $resolver->setRequired('current_user');
    }
}
