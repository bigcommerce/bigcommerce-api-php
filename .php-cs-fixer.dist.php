<?php
 return (new PhpCsFixer\Config())
        ->setFinder(
            PhpCsFixer\Finder::create()
                ->files()
                ->in(__DIR__)
                ->name("*.php")
                ->ignoreVCSIgnored(true)
        )
        ->setRules([
            '@PSR2' => true,
        ]);
