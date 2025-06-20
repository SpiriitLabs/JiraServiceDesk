<?php

namespace App\Form\App\Favorite;

use App\Controller\App\Favorite\RouteCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;

class FavoriteFormType extends AbstractType
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', HiddenType::class)
            ->add('projectId', HiddenType::class)
            ->add('name', HiddenType::class)
            ->add('link', HiddenType::class)
            ->setAction(
                $this->router->generate(
                    RouteCollection::FAVORITE_STREAM->prefixed(),
                    [
                        'code' => $builder->getData()['code'],
                        'projectId' => $builder->getData()['projectId'],
                    ]
                )
            )
        ;
    }
}
