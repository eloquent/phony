<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__)
    ->exclude(
        array(
            'build',
            'coverage',
            'test/fixture/mock-generator/return-type', // weird under PHP 7
            'vendor',
            'web',
        )
    );

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers(
        array(
            // symfony
            'array_element_no_space_before_comma',
            'array_element_white_space_after_comma',
            'blankline_after_open_tag',
            'duplicate_semicolon',
            'extra_empty_lines',
            'function_typehint_space',
            'include',
            'join_function',
            'list_commas',
            'multiline_array_trailing_comma',
            'namespace_no_leading_whitespace',
            'new_with_braces',
            'no_blank_lines_after_class_opening',
            'no_empty_lines_after_phpdocs',
            'object_operator',
            'operators_spaces',
            'phpdoc_indent',
            'phpdoc_params',
            'phpdoc_scalar',
            'phpdoc_short_description',
            'phpdoc_to_comment',
            'phpdoc_trim',
            'phpdoc_type_to_var',
            'phpdoc_types',
            'phpdoc_var_without_name',
            'pre_increment',
            'print_to_echo',
            'remove_leading_slash_use',
            'remove_lines_between_uses',
            'return',
            'self_accessor',
            'short_bool_cast',
            'single_array_no_trailing_comma',
            'single_blank_line_before_namespace',
            'single_quote',
            'spaces_before_semicolon',
            'spaces_cast',
            'standardize_not_equal',
            'ternary_spaces',
            'trim_array_spaces',
            'unary_operators_spaces',
            'unneeded_control_parentheses',
            'unused_use',
            'whitespacy_lines',

            // contrib
            'concat_with_spaces',
            'multiline_spaces_before_semicolon',
            'ordered_use',

            // specific to this project
            'long_array_syntax',
        )
    )
    ->finder($finder);
