<?php

declare(strict_types=1);

namespace App\Form\App\Issue;

use App\Enum\User\Role;
use App\Message\Command\App\Issue\EditIssue;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditIssueFormType extends AbstractIssueFormType
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('transition', ChoiceType::class, [
                'required' => true,
                'choices' => $options['transitions'],
            ])
        ;

        if ($this->security->isGranted(Role::ROLE_APP_CAN_ASSIGNEE)) {
            $builder
                ->add('assignee', ChoiceType::class, [
                    'required' => true,
                    'choices' => $options['assignees'],
                    'disabled' => ! $options['assignee_editable'],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => EditIssue::class,
            'translation_domain' => 'app',
            'label_format' => 'issue.%name%.label',
            'projectId' => null,
            'transitions' => [],
            'assignee_editable' => true,
            'assignees' => [],
        ]);
    }
}
