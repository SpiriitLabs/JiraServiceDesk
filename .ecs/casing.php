<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Squiz\Sniffs\NamingConventions\ValidFunctionNameSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\NamingConventions\ValidVariableNameSniff;
use PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rules([
        LowercaseStaticReferenceFixer::class,
        NativeFunctionCasingFixer::class,
    ]);

    $ecsConfig->skip([
        ValidVariableNameSniff::class . '.PrivateNoUnderscore' => null,
        ValidFunctionNameSniff::class . '.PrivateNoUnderscore' => null,
    ]);
};
