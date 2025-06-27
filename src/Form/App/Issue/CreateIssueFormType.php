<?php

namespace App\Form\App\Issue;

use App\Entity\Project;
use App\Entity\User;
use App\Form\Type\QuillAdfType;
use App\Message\Command\App\Issue\CreateIssue;
use App\Repository\PriorityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateIssueFormType extends AbstractIssueFormType
{
    public function __construct(
        Security $security,
        #[Autowire(env: 'DEFAULT_PRIORITY_NAME')]
        private readonly string $defaultPriorityName,
        private readonly PriorityRepository $priorityRepository,
    ) {
        parent::__construct($security);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $builder->getData()
            ->creator
        ;
        /** @var Project $project */
        $project = $builder->getData()
            ->project
        ;

        if ($builder->getData()->priority == null) {
            $builder->getData()
                ->priority = $this->priorityRepository->findOneBy([
                    'name' => $this->defaultPriorityName,
                ])
            ;
        }

        if ($builder->getData()->type == null) {
            $builder->getData()
                ->type = $builder->getData()
                ->project->defaultIssueType
            ;
        }

        $options['projectId'] = $project->getId();
        parent::buildForm($builder, $options);

        if ($project !== null && $user->getProjects()->contains($project) == false) {
            $project = null;
        }

        $builder
            ->add('description', QuillAdfType::class, [
                'required' => true,
                'attr' => [
                    'style' => 'height: 250px',
                ],
            ])
            ->add('attachments', FileType::class, [
                'required' => false,
                'multiple' => true,
                'label' => 'issue.attachment.label',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => CreateIssue::class,
            'translation_domain' => 'app',
            'label_format' => 'issue.%name%.label',
        ]);
    }
}
