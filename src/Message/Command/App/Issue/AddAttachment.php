<?php

declare(strict_types=1);

namespace App\Message\Command\App\Issue;

use JiraCloud\Issue\Issue;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AddAttachment
{
    public function __construct(
        public Issue $issue,
        public UploadedFile $file,
    ) {
    }
}
