<?php

use Delight\ExtendedTokens\ExtendedTokens;

require __DIR__ . '/vendor/autoload.php';

$tt = new ExtendedTokens();

$parsed = $tt->parse(
    file_get_contents(__DIR__ . '/scratch.php')
);

