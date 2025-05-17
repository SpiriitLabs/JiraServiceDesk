<?php

namespace App\Form\App\Issue;

use App\Entity\Project;
use App\Entity\User;
use App\Form\Type\QuillAdfType;
use App\Message\Command\App\Issue\CreateIssue;
use App\Repository\ProjectRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateIssueFormType extends AbstractIssueFormType
{
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

        $options['projectId'] = $project->getId();
        parent::buildForm($builder, $options);

        if ($project !== null && $user->getProjects()->contains($project) == false) {
            $project = null;
        }

        $builder
            ->add('description', QuillAdfType::class, [
                'required' => true,
                'attr' => [
                    'style' => "height: 250px"
                ]
            ])
            ->add('attachments', FileType::class, [
                'required' => false,
                'multiple' => true,
                'label' => 'issue.attachment.label',
            ])
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'required' => true,
                'multiple' => false,
                'autocomplete' => true,
                'label' => 'project.label',
                'choice_label' => 'name',
                'query_builder' => function (ProjectRepository $projectRepository) use ($user) {
                    return $projectRepository->getByUser($user);
                },
                'data' => $project,
                'attr' => [
                    'readonly' => 'true',
                ],
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
