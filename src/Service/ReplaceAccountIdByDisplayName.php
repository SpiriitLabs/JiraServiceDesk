<?php

namespace App\Service;

use App\Repository\Jira\UserRepository;

class ReplaceAccountIdByDisplayName
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function replaceInCommentBody(string $comment): string
    {
        if (preg_match_all('#\[~accountid:([a-zA-Z0-9\-:]+)\]#', $comment, $matches)) {
            $test = array_combine($matches[0], $matches[1]);
            foreach ($test as $match => $id) {
                $account = $this->userRepository->getUserById($id);
                if ($account !== null) {
                    $comment = str_replace($match, $account->displayName, $comment);
                }
            }
        }

        return $comment;
    }
}
