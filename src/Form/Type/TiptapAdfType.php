<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TiptapAdfType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'initial_adf' => null,
            'issue_key' => '',
            'attachment_map' => '{}',
        ]);

        $resolver->setAllowedTypes('initial_adf', ['null', 'string']);
        $resolver->setAllowedTypes('issue_key', 'string');
        $resolver->setAllowedTypes('attachment_map', 'string');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['initial_adf'] = $options['initial_adf'];
        $view->vars['issue_key'] = $options['issue_key'];
        $view->vars['attachment_map'] = $options['attachment_map'];
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'tiptap_adf';
    }
}
