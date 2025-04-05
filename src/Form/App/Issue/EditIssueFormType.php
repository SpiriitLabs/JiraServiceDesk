<?php

namespace App\Form\App\Issue;

use App\Message\Command\App\Issue\EditIssue;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditIssueFormType extends AbstractIssueFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('transition', ChoiceType::class, [
                'required' => true,
                'choices' => $options['transitions'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EditIssue::class,
            'translation_domain' => 'app',
            'label_format' => 'issue.%name%.label',
            'projectId' => null,
            'transitions' => [],
        ]);
    }
}
