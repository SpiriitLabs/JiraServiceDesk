<?php

namespace App\Form\Filter;

use App\Entity\Project;
use App\Form\AbstractFilterType;
use App\Repository\ProjectRepository;
use Spiriit\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\FormBuilderInterface;

class UserFormFilter extends AbstractFilterType
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
    ) {
    }

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
                        $expr->like(
                            'CONCAT(' . $alias . '.lastName, \' \', ' . $alias . '.firstName)',
                            $expr->literal($value)
                        ),
                        $expr->like(
                            'CONCAT(' . $alias . '.firstName, \' \', ' . $alias . '.lastName)',
                            $expr->literal($value)
                        ),
                        $expr->like($alias . '.email', $expr->literal($value)),
                        $expr->like($alias . '.company', $expr->literal($value)),
                    );

                    return $query->createCondition((string) $condition);
                },
            ])
            ->add('projects', EntityFilterType::class, [
                'class' => Project::class,
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'label' => false,
                'attr' => [
                    'placeholder' => 'project.label',
                ],
                'choice_label' => 'name',
            ])
        ;
    }
}
