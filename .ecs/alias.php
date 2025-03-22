<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Alias\EregToPregFixer;
use PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer;
use PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer;
use PhpCsFixer\Fixer\Alias\PowToExponentiationFixer;
use PhpCsFixer\Fixer\Naming\NoHomoglyphNamesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rules([
        EregToPregFixer::class,
        NoAliasFunctionsFixer::class,
        NoHomoglyphNamesFixer::class,
        PowToExponentiationFixer::class,
    ]);

    $ecsConfig->ruleWithConfiguration(NoMixedEchoPrintFixer::class, [
        'use' => 'echo',
    ]);
};
