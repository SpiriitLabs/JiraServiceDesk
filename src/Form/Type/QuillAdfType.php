<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuillAdfType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'editor_toolbar' => [
                ['bold', 'italic', 'underline', 'strike'],
                [
                    [
                        'header' => [false, 1, 2, 3, 4, 5, 6],
                    ],
                    'blockquote',
                    'code-block',
                ],
                [[
                    'list' => 'ordered',
                ], [
                    'list' => 'bullet',
                ], [
                    'script' => 'sub',
                ], [
                    'script' => 'super',
                ], [
                    'indent' => '-1',
                ], [
                    'indent' => '+1',
                ], [
                    'direction' => 'rtl',
                ]],
                [[
                    'size' => [
                        'small',
                        false,
                        'large',
                        'huge',
                    ],
                ]],
                [[
                    'font' => [],
                ]],
                [[
                    'align' => [],
                ]], ['link'], ['clean'], ['formula'],
            ],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['toolbarOptions'] = $options['editor_toolbar'];
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'quill_adf';
    }
}
