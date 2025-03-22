<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Operator\IncrementStyleFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\OperatorLinebreakFixer;
use PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer;
use PhpCsFixer\Fixer\Operator\TernaryToNullCoalescingFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rules([
        IncrementStyleFixer::class,
        ObjectOperatorWithoutWhitespaceFixer::class,
        OperatorLinebreakFixer::class,
        StandardizeNotEqualsFixer::class,
        TernaryToNullCoalescingFixer::class,
    ]);
};
