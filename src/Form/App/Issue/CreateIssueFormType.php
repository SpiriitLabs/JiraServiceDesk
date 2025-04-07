<?php

namespace App\Form\App\Issue;

use App\Message\Command\App\Issue\CreateIssue;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateIssueFormType extends AbstractIssueFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('attachments', FileType::class, [
                'required' => false,
                'multiple' => true,
                'label' => 'issue.attachment.label',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateIssue::class,
            'translation_domain' => 'app',
            'label_format' => 'issue.%name%.label',
            'projectId' => null,
        ]);
    }
}
