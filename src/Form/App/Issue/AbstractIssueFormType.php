<?php

namespace App\Form\App\Issue;

use App\Entity\IssueType;
use App\Entity\Priority;
use App\Enum\User\Role;
use App\Message\Command\App\Issue\AbstractIssueDTO;
use App\Repository\IssueTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AbstractIssueFormType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

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
            ->add('priority', EntityType::class, [
                'required' => true,
                'class' => Priority::class,
                'choice_label' => 'name',
            ])
        ;

        if ($this->security->isGranted(Role::ROLE_APP_CAN_ASSIGNEE)) {
            $builder
                ->add('assignee', ChoiceType::class, [
                    'required' => true,
                    'choices' => $options['assignees'],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AbstractIssueDTO::class,
            'translation_domain' => 'app',
            'label_format' => 'issue.%name%.label',
            'projectId' => null,
            'assignees' => [],
        ]);
    }
}
