<?php

namespace App\Form\Admin\User;

use App\Entity\Project;
use App\Enum\User\Role;
use App\Form\Type\ChoiceSwitchType;
use App\Form\Type\SwitchType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminUserFormType extends AbstractUserFormType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('company', TextType::class, [
                'required' => false,
            ])
            ->add('roles', ChoiceSwitchType::class, [
                'multiple' => true,
                'required' => true,
                'expanded' => true,
                'choices' => Role::getList(),
                'choice_label' => function ($choice, $key, $value) {
                    return $this->translator->trans(id: $key, domain: 'app');
                },
            ])
            ->add('projects', EntityType::class, [
                'class' => Project::class,
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'label' => 'project.label',
                'choice_label' => 'name',
            ])
            ->add('enabled', SwitchType::class)
        ;
    }
}
