<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderStaticFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rules([
        IncludeFixer::class,
        NoSuperfluousElseifFixer::class,
        TrailingCommaInMultilineFixer::class,
        YodaStyleFixer::class,
        DeclareStrictTypesFixer::class,
        VoidReturnFixer::class,
        PhpUnitDataProviderStaticFixer::class,
    ]);
};
