<?php

namespace App\Form\App\Issue;

use App\Entity\IssueType;
use App\Enum\Issue\Priority;
use App\Message\Command\App\Issue\AbstractIssueDTO;
use App\Repository\IssueTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class IssueFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('summary', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('type', EntityType::class, [
                'class' => IssueType::class,
                'choice_label' => 'name',
                'query_builder' => function (IssueTypeRepository $itr) use ($options) {
                    return $itr->createQueryBuilder('o')
                        ->where('o.project = :project')
                        ->setParameter('project', $options['projectId'])
                    ;
                },
            ])
            ->add('priority', EnumType::class, [
                'required' => true,
                'class' => Priority::class,
                'choice_label' => fn (Priority $priority) => $priority->label(),
                'constraints' => [new NotBlank()],
                'empty_data' => Priority::NORMAL,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AbstractIssueDTO::class,
            'translation_domain' => 'app',
            'label_format' => 'issue.%name%.label',
            'projectId' => null,
        ]);
    }
}
