<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;
use SlevomatCodingStandard\Sniffs\Commenting\ForbiddenAnnotationsSniff;
use SlevomatCodingStandard\Sniffs\Commenting\InlineDocCommentDeclarationSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rules([
        NoEmptyCommentFixer::class,
        InlineDocCommentDeclarationSniff::class,
    ]);

    $ecsConfig->ruleWithConfiguration(SingleLineCommentStyleFixer::class, [
        'comment_types' => ['hash'],
    ]);

    $ecsConfig->ruleWithConfiguration(ForbiddenAnnotationsSniff::class, [
        'forbiddenAnnotations' => [
            '@api',
            '@author',
            '@category',
            '@copyright',
            '@created',
            '@license',
            '@package',
            '@since',
            '@subpackage',
            '@version',
        ],
    ]);
};
