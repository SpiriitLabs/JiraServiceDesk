<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rules([
        IncludeFixer::class,
        NoSuperfluousElseifFixer::class,
        TrailingCommaInMultilineFixer::class,
    ]);
};
