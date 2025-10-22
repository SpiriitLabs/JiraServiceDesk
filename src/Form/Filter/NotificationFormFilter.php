<?php

declare(strict_types=1);

namespace App\Form\Filter;

use App\Entity\User;
use App\Enum\Notification\NotificationType;
use App\Form\AbstractFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationFormFilter extends AbstractFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('notificationType', EnumType::class, [
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'class' => NotificationType::class,
                'choice_label' => fn (NotificationType $logType) => $logType->label(),
                'apply_filter' => function (ORMQuery $query, string $field, array $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $alias = $query->getRootAlias();
                    $filterValues = array_map(function (NotificationType $logType) {
                        return $logType->value;
                    }, $values['value']);

                    $expr = $query->getExpr();
                    $condition = $expr->in($alias . '.notificationType', $filterValues);

                    return $query->createCondition((string) $condition);
                },
            ])
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
                        $expr->like($alias . '.subject', $expr->literal($value)),
                        $expr->like($alias . '.body', $expr->literal($value)),
                    );

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
