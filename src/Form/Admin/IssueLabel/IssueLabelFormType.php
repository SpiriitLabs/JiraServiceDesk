<?php

declare(strict_types=1);

namespace App\Form\Admin\IssueLabel;

use App\Entity\User;
use App\Message\Command\Admin\IssueLabel\AbstractIssueLabelDTO;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class IssueLabelFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jiraLabel', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Length(max: 255),
                ],
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Length(max: 255),
                ],
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'multiple' => true,
                'required' => false,
                'autocomplete' => true,
                'attr' => [
                    'data-controller' => 'form-control select2',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AbstractIssueLabelDTO::class,
            'translation_domain' => 'app',
            'label_format' => 'issueLabel.%name%.label',
        ]);
    }
}
