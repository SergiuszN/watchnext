<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
//    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PHP82Migration' => true,
        '@PSR12' => true,
        '@Symfony' => true,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'concat_space' => ['spacing' => 'one'],
        'global_namespace_import' => true,
    ])
    ->setFinder($finder)
;
