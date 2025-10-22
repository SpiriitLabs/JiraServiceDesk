<?php

declare(strict_types=1);

namespace App\Form\Filter;

use App\Entity\User;
use App\Form\AbstractFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\FormBuilderInterface;

class ProjectFormFilter extends AbstractFilterType
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
                        $expr->like($alias . '.jiraId', $expr->literal($value)),
                        $expr->like($alias . '.jiraKey', $expr->literal($value)),
                        $expr->like($alias . '.name', $expr->literal($value)),
                        $expr->like($alias . '.description', $expr->literal($value)),
                    );

                    return $query->createCondition((string) $condition);
                },
            ])
            ->add('users', EntityFilterType::class, [
                'class' => User::class,
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'label' => false,
                'attr' => [
                    'placeholder' => 'user.label',
                ],
                'choice_label' => 'fullName',
            ])
        ;
    }
}
