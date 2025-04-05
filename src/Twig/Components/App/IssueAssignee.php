<?php

namespace App\Twig\Components\App;

use JiraCloud\Issue\Reporter;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent(
    name: 'issue:assignee',
    template: 'components/app/issue/assignee.html.twig',
)]
class IssueAssignee
{
    public ?Reporter $reporter = null;

    public function __construct(
    ) {
    }

    #[PreMount]
    public function preMount(array $data): array
    {
        $resolver = new OptionsResolver();
        $resolver->setAllowedTypes('reporter', [null, Reporter::class]);

        return $resolver->resolve($data);
    }
}
