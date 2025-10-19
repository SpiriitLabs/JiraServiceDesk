<?php

namespace App\Form\Filter;

use App\Entity\User;
use App\Enum\LogEntry\Level;
use App\Enum\LogEntry\Type;
use App\Form\AbstractFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LogEntryFormFilter extends AbstractFilterType
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
            ->add('type', EnumType::class, [
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'attr' => [
                    'placeholder' => 'logs.type.label',
                ],
                'class' => Type::class,
                'choice_label' => fn (Type $type) => $type->label(),
                'apply_filter' => function (ORMQuery $query, string $field, array $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $alias = $query->getRootAlias();
                    $filterValues = array_map(function (Type $type) {
                        return $type->value;
                    }, $values['value']);

                    $expr = $query->getExpr();
                    $condition = $expr->in($alias . '.type', $filterValues);

                    return $query->createCondition((string) $condition);
                },
            ])
            ->add('level', EnumType::class, [
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'attr' => [
                    'placeholder' => 'logs.level.label',
                ],
                'class' => Level::class,
                'choice_label' => fn (Level $level) => $level->label(),
                'apply_filter' => function (ORMQuery $query, string $field, array $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $alias = $query->getRootAlias();
                    $filterValues = array_map(function (Level $level) {
                        return $level->value;
                    }, $values['value']);

                    $expr = $query->getExpr();
                    $condition = $expr->in($alias . '.level', $filterValues);

                    return $query->createCondition((string) $condition);
                },
            ])
            ->add('user', EntityFilterType::class, [
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'translation_domain' => 'app',
        ]);
    }
}
