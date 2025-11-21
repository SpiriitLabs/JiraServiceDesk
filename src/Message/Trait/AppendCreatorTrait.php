<?php

declare(strict_types=1);

namespace App\Message\Trait;

use App\Entity\User;

trait AppendCreatorTrait
{
    /**
     * @param array<int,mixed> $data
     *
     * @return array<mixed>
     */
    private function appendCreator(User $creator, array $data): array
    {
        $data['content'][] = [
            'type' => 'paragraph',
            'content' => [
                [
                    'type' => 'text',
                    'text' => '--------------',
                ],
            ],
        ];
        $data['content'][] = [
            'type' => 'paragraph',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $creator->getFullName(),
                ],
            ],
        ];

        return $data;
    }
}
