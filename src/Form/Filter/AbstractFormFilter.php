<?php

declare(strict_types=1);

namespace App\Form\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractFormFilter extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'translation_domain' => 'app',
            'label_format' => 'filter.%name%.label',
        ]);
    }
}
