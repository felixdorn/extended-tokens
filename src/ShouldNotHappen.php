<?php

declare(strict_types=1);

namespace Delight\ExtendedTokens;

use RuntimeException;

final class ShouldNotHappen extends RuntimeException
{
    public static function notConvertedStringToken(array $token): ShouldNotHappen
    {
        return new self(sprintf('A T_STRING token [%s, %s] has not been converted to a more concise value.', ...$token));
    }
}
