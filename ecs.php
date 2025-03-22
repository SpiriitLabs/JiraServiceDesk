<?php

declare(strict_types=1);

use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->parallel();

    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/config/bundles.php',
        __DIR__ . '/.ecs',
        __DIR__ . '/ecs.php',
        __DIR__ . '/bin/console',
        __DIR__ . '/public/index.php',
    ]);

    $ecsConfig->import(__DIR__ . '/.ecs/alias.php');
    $ecsConfig->import(__DIR__ . '/.ecs/arrays.php');
    $ecsConfig->import(__DIR__ . '/.ecs/basic.php');
    $ecsConfig->import(__DIR__ . '/.ecs/casing.php');
    $ecsConfig->import(__DIR__ . '/.ecs/cast.php');
    $ecsConfig->import(__DIR__ . '/.ecs/class-definition.php');
    $ecsConfig->import(__DIR__ . '/.ecs/comment.php');
    $ecsConfig->import(__DIR__ . '/.ecs/constant.php');
    $ecsConfig->import(__DIR__ . '/.ecs/controll-structure.php');
    $ecsConfig->import(__DIR__ . '/.ecs/language-construct.php');
    $ecsConfig->import(__DIR__ . '/.ecs/operator.php');
    $ecsConfig->import(__DIR__ . '/.ecs/phpdoc.php');
    $ecsConfig->import(__DIR__ . '/.ecs/spacing.php');

    $ecsConfig->dynamicSets(['@Symfony']);

    $ecsConfig->sets([
        SetList::COMMON,
        SetList::PSR_12,
        SetList::CLEAN_CODE,
        SetList::SYMPLIFY,
    ]);

    $ecsConfig->ruleWithConfiguration(LineLengthFixer::class, [
        'inline_short_lines' => false,
    ]);
};
