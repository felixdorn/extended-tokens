<?php

/** @var string $a */
$x = (bool)$a;

class X
{
    public string $x1;

    public function c(string $d, ...$l): object
    {
        $a = new stdClass();
        $a->b = 1 ** 1;
        return $a;
    }
}

function n(X $x, string $b): X
{
    return new X;
}

$a = [[
    'a' => 'b'
]];

if (true && false || true) {
    echo true;
}

switch (1) {
    case 1:
        echo '1' . 1;
        break;
    case 2:
    case 8:
        break;
    default:
        echo '6';
        break;
}

fn(callable $cb) => $cb()

foreach ([1, 2, 3] as $k => $v) {
    echo $v;

    continue;
}

echo __CLASS__;

try {
    $that = true;
} catch (\Throwable $exception) {
    $catched = 'throwable';
}

$x &= $a;

$tmp = fn() => 1 + 1;

token_get_all('<?php');

clone new stdClass();

$a ?? 1;
$a ??= 1;

$x1 = 'a';

$x1 .= 'b';

$xInstance = new X;

$xInstance->{$x1};


echo "{$x1}";

const A = 'b';
?>

<div>
    <?= 'Hello world' ?>
</div>

