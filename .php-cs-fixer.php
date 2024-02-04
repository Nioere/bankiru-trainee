<?php

$rules = [
    '@Symfony' => true,
    'array_syntax' => ['syntax' => 'short'],
    'phpdoc_align' => false,

    'combine_consecutive_unsets' => true,
    'heredoc_to_nowdoc' => true,
    'global_namespace_import' => true,
    'phpdoc_order' => true,
    'phpdoc_to_comment' => false,
    'no_useless_return' => true,
    'binary_operator_spaces' => true,
    'concat_space' => ['spacing' => 'one'],
    'visibility_required' => ['elements' => ['property', 'method']],
    'declare_strict_types' => true,
    'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
];

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/public',
        __DIR__ . '/tests',
    ])
    ->exclude([
        'var',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

$config = new PhpCsFixer\Config();

return $config
    ->setRules($rules)
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ;
