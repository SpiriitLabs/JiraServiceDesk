<?php

namespace App\Form\Filter;

use App\Form\AbstractFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\FormBuilderInterface;

class EmailLogFormFilter extends AbstractFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', TextFilterType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'filter.query.label',
                ],
                'apply_filter' => function (ORMQuery $query, string $field, array $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $value = \sprintf('%%%s%%', $values['value']);
                    $alias = $query->getRootAlias();

                    $expr = $query->getExpr();
                    $condition = $expr->orX(
                        $expr->like($alias . '.recipient', $expr->literal($value)),
                        $expr->like($alias . '.subject', $expr->literal($value)),
                    );

                    return $query->createCondition((string) $condition);
                },
            ])
        ;
    }
}
