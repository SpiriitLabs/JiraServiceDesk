<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\IssueLabel;

use App\Entity\IssueLabel;

class DeleteIssueLabel
{
    public function __construct(
        public IssueLabel $issueLabel,
    ) {
    }
}
