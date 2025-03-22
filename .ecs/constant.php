<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rules([NativeConstantInvocationFixer::class]);
};
