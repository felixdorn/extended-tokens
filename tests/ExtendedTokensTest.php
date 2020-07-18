<?php

use Delight\ExtendedTokens\ExtendedTokens;

it('can parse', function () {
    $et = new Delight\ExtendedTokens\ExtendedTokens();

    $tokens = $et->parse('<?php $a = "b";');

    assertEquals([
        [T_OPEN_TAG, '<?php '],
        [T_VARIABLE, '$a'],
        [T_WHITESPACE, ' '],
        [T_EQUAL, '='],
        [T_WHITESPACE, ' '],
        [T_CONSTANT_ENCAPSED_STRING, '"b"'],
        [-1, ';'],
    ], $tokens);
});
it('can replace T_STRING by T_VARIABLE_TYPE', function () {
    $sample = '<?php function a(B $b): object {}';
    $et = new ExtendedTokens();
    $tokens = $et->parse($sample);
    assertEquals([T_CLASS_NAME, 'B'], $tokens[5]);
    assertEquals([T_VARIABLE_TYPE, 'object'], $tokens[11]);

    $sample = '<?php function a                           (B $b): object {}';
    $et = new ExtendedTokens();
    $tokens = $et->parse($sample);
    assertEquals([T_CLASS_NAME, 'B'], $tokens[6]);
    assertEquals([T_VARIABLE_TYPE, 'object'], $tokens[12]);
});
it('can replace T_STRING by T_CLASS_NAME when instantiating a class', function () {
    $sample = '<?php class A {} $b = new A;';
    $et = new ExtendedTokens();
    $tokens = $et->parse($sample);

    assertEquals([T_CLASS_NAME, 'A'], $tokens[14]);
});
it('can replace T_STRING by T_CLASS_NAME when defining a class', function () {
    $sample = '<?php class B {}';
    $et = new ExtendedTokens();
    $tokens = $et->parse($sample);

    assertEquals([T_CLASS_NAME, 'B'], $tokens[3]);
});
it('can replace T_STRING by T_CONST_NAME', function () {
    $sample = '<?php const HELLO = "world"';
    $et = new ExtendedTokens();
    $tokens = $et->parse($sample);

    assertEquals([T_CONST_NAME, 'HELLO'], $tokens[3]);
});
it('can replace T_STRING by T_EQUAL', function () {
    $sample = '<?php $a = 1';
    $et = new ExtendedTokens();
    $tokens = $et->parse($sample);

    assertEquals([T_EQUAL, '='], $tokens[3]);
});
it('can replace T_STRING by T_FUNCTION_NAME', function () {
    $sample = '<?php function hello() {}';
    $et = new ExtendedTokens();
    $tokens = $et->parse($sample);

    assertEquals([T_FUNCTION_NAME, 'hello'], $tokens[3]);
});
it('can replace $object->{T_STRING} by T_VARIABLE', function () {
    $sample = '<?php class A {} $b = new A; $b->a';
    $et = new ExtendedTokens();
    $tokens = $et->parse($sample);
    assertEquals([T_VARIABLE, 'a'], $tokens[19]);
});
it('can replace true/false by T_TRUE and T_FALSE', function () {
    $sample = '<?php true && false;';
    $et = new ExtendedTokens();
    $tokens = $et->parse($sample);

    assertEquals([T_TRUE, 'true'], $tokens[1]);
    assertEquals([T_FALSE, 'false'], $tokens[5]);
});
it('can replace concatenation . by T_CONCAT', function () {
    $sample = '<?php "hello" . "world"';
    $et = new ExtendedTokens();
    $tokens = $et->parse($sample);

    assertEquals([T_CONCAT, '.'], $tokens[3]);
});
it('defines T_* constants', function () {
    assertTrue(defined('T_EQUAL'));
    assertTrue(defined('T_CONCAT'));
    assertTrue(defined('T_FUNCTION_NAME'));
    assertTrue(defined('T_CLASS_NAME'));
    assertTrue(defined('T_VARIABLE_TYPE'));
    assertTrue(defined('T_TRUE'));
    assertTrue(defined('T_FALSE'));
});
it('can replace T_STRING by T_VARIABLE_TYPE in short closures', function () {
    $sample = '<?php fn (object $a): B => 4';
    $tokens = (new ExtendedTokens())->parse($sample);
    assertEquals([T_VARIABLE_TYPE, 'object'], $tokens[4]);

    $sample = '<?php fn (object &$a): B => 4';
    $tokens = (new ExtendedTokens())->parse($sample);
    assertEquals([T_VARIABLE_TYPE, 'object'], $tokens[4]);
});
