<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/test',
        __DIR__ . '/src',
    ]);


    // define sets of rules
    $rectorConfig->sets([LevelSetList::UP_TO_PHP_81, PHPUnitSetList::PHPUNIT_100,]);
};
