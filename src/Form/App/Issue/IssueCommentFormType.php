<?php

declare(strict_types=1);

namespace App\Form\App\Issue;

use App\Message\Command\App\Issue\CreateComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IssueCommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class, [
                'required' => true,
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
            'data_class' => CreateComment::class,
            'translation_domain' => 'app',
            'label_format' => 'issue.%name%.label',
        ]);
    }
}
