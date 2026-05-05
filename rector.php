<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        sprintf('%s/src', __DIR__),
        sprintf('%s/config', __DIR__),
        sprintf('%s/tests', __DIR__),
    ])
    ->withRootFiles()
    ->withSkip([
        sprintf('%s/vendor', __DIR__),
        sprintf('%s/workbench', __DIR__),
    ])
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    )
    ->withSets([
        LaravelLevelSetList::UP_TO_LARAVEL_120,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_IF_HELPERS,
    ])
    ->withRules([
        DeclareStrictTypesRector::class,
    ])
    ->withCache(sprintf('%s/.rector-cache', __DIR__))
    ->withImportNames(removeUnusedImports: true);
