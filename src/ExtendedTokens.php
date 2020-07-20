<?php

declare(strict_types=1);

namespace Delight\ExtendedTokens;

class ExtendedTokens
{
    public static $keywords = [
        T_ABSTRACT,
        T_ARRAY,
        T_FOREACH,
        T_AS,
        T_ECHO,
        T_TRY,
        T_PRINT,
        T_CATCH,
        T_CLONE,
        T_CLOSE_TAG,
        T_SWITCH,
        T_CASE,
        T_BREAK,
        T_DEFAULT,
        T_OPEN_TAG,
        T_OPEN_TAG_WITH_ECHO,
        T_CLASS,
        T_PROTECTED,
        T_PUBLIC,
        T_PRIVATE,
        T_FUNCTION,
        T_NEW,
        T_RETURN,
        T_FN,
        T_CONST,
        T_CONTINUE,
        T_DO,
        T_ELSE,
        T_IF,
        T_ELSEIF,
        T_EMPTY,
        T_WHILE,
        T_ENDDECLARE,
        T_ENDFOR,
        T_ENDFOREACH,
        T_ENDIF,
        T_ENDSWITCH,
        T_ENDWHILE,
        T_START_HEREDOC,
        T_END_HEREDOC,
        T_EXIT,
        T_EVAL,
        T_EXTENDS,
        T_FINALLY,
        T_FINAL,
        T_FOR,
        T_GLOBAL,
        T_GOTO,
        T_HALT_COMPILER,
        T_IMPLEMENTS,
        T_INCLUDE,
        T_REQUIRE,
        T_INSTANCEOF,
        T_INSTEADOF,
        T_INTERFACE,
        T_ISSET,
        T_LIST,
        T_LOGICAL_AND,
        T_LOGICAL_XOR,
        T_LOGICAL_OR,
        T_NAMESPACE,
        T_PRINT,
        T_REQUIRE_ONCE,
        T_INCLUDE_ONCE,
        T_STATIC,
        T_THROW,
        T_TRAIT,
        T_UNSET,
        T_USE,
        // Note: This is was used back in PHP4, and can be considered as a synonym for public in a property declaration
        T_VAR,
        T_YIELD,
        T_YIELD_FROM,
    ];
    public static $casts = [
        T_BOOL_CAST,
        T_ARRAY_CAST,
        T_DOUBLE_CAST,
        T_INT_CAST,
        T_UNSET_CAST,
        T_OBJECT_CAST,
        T_STRING_CAST,
    ];
    public static $constants = [
        T_CLASS_C,
        T_METHOD_C,
        T_NS_C,
        T_FUNC_C,
        T_TRAIT_C,
        T_DIR,
        T_FILE,
        T_LINE,
    ];
    public static $types = ['string', 'int', 'float', 'object', 'callable', 'array', 'iterable', 'bool', 'self'];

    public function parse(string $code, bool $withOffsets = false): array
    {
        $tokens = [];
        $parsed = token_get_all(trim($code));

        foreach ($parsed as $k => $token) {
            $tokens[] = $this->createToken($token);
        }

        foreach ($tokens as $k => $token) {
            if ($token[0] !== T_STRING) {
                continue;
            }

            [$kind, $value] = $this->processStringToken($token, $k, $tokens);

            $tokens[$k] = [$kind, $value, $k];
        }

        return array_map(function ($token) use ($withOffsets) {
            if ($withOffsets) {
                return $token;
            }

            return [$token[0], $token[1]];
        }, $this->withOptimizedNamespace($tokens));
    }

    /**
     * @param string|array $token
     */
    private function createToken($token): array
    {
        if ($token === '.') {
            return [T_CONCAT, '.'];
        }
        if ($token === '=') {
            return [T_EQUAL, '='];
        }
        if ($token === '&') {
            return [T_REF, '&'];
        }
        if ($token === '!') {
            return [T_NEGATION, '!'];
        }
        if (is_string($token)) {
            return [-1, $token];
        }

        return $token;
    }

    private function processStringToken(array $token, int $index, array $tokens): array
    {
        // We know that there is always at least one token before a T_STRING token
        // ex: class {T_STRING} {}, function {T_STRING}() {}
        [$previousType, $previousValue] = $tokens[$index - 1];
        $valuableIndex                  = $index;

        while ($previousType === T_WHITESPACE) {
            [$previousType, $previousValue] = $tokens[--$valuableIndex];
        }

        if ($previousType === T_IMPLEMENTS || $previousType === T_EXTENDS || $previousType === T_USE) {
            return [T_CLASS_NAME, $token[1]];
        }

        if ($previousType === T_FUNCTION) {
            return [T_FUNCTION_NAME, $token[1]];
        }

        if ($previousType === T_CLASS) {
            return [T_CLASS_NAME, $token[1]];
        }

        if ($previousType === T_CONST) {
            return [T_CONST_NAME, $token[1]];
        }

        if ($previousType === T_NEW) {
            return [T_CLASS_NAME, $token[1]];
        }

        if ($previousValue === '->') {
            if (($tokens[$index + 1][1] ?? '') === '(') {
                return [T_FUNCTION_NAME, $token[1]];
            }

            return [T_VARIABLE, $token[1]];
        }

        if ($token[1] === 'true') {
            return [T_TRUE, 'true'];
        }

        if ($token[1] === 'false') {
            return [T_FALSE, 'false'];
        }

        if (
            $previousType === T_NAMESPACE ||
            $previousType === T_NS_SEPARATOR ||
            ($this->getNextToken($valuableIndex, $tokens)[1] === '\\')
        ) {
            return [T_NAMESPACE_PART, $token[1]];
        }

        if (
            $previousValue === ':' ||
            $previousValue === ',' ||
            $previousValue === T_NS_SEPARATOR ||
            ($previousValue === '(' && $this->getLastKeyword($valuableIndex, $tokens)[0] === T_FUNCTION) ||
            ($previousValue === '(' && $this->getLastKeyword($valuableIndex, $tokens)[0] === T_FN)
        ) {
            if (in_array($token[1], static::$types, true)) {
                return [T_VARIABLE_TYPE, $token[1]];
            }

            return [T_CLASS_NAME, $token[1]];
        }

        if ($token[0] === T_VARIABLE && ($previousType === T_FUNCTION || $previousType === T_FN)) {
            return [T_PARAMETER, $token[1]];
        }

        // @codeCoverageIgnoreStart
        return $token;
        // @codeCoverageIgnoreEnd
    }

    public function getNextToken(int $index, array $tokens): array
    {
        $current = $tokens[++$index];

        while ($current[0] === T_WHITESPACE) {
            $current = $tokens[++$index];
        }

        return $current;
    }

    public function getLastKeyword(int $index, array $tokens): array
    {
        $current = $tokens[$index];

        while (!in_array($current[0], self::$keywords, true)) {
            $current = $tokens[--$index];
        }

        return $tokens[$index];
    }

    private function withOptimizedNamespace(array $tokens): array
    {
        $namespaces = array_filter($tokens, function ($token) {
            return in_array($token[0], [T_NS_SEPARATOR, T_NAMESPACE_PART], true);
        });
        $namespaces = array_values($namespaces);
        $separated  = function (int $key, array $namespaces) {
            if (!array_key_exists($key - 1, $namespaces)) {
                return true;
            }

            return !($namespaces[$key][0] !== T_NS_SEPARATOR && $namespaces[$key - 1][0] !== T_NS_SEPARATOR);
        };

        $chunks     = [];
        $chunkStart = 0;

        foreach (array_keys($namespaces) as $k) {
            if (!$separated($k, $namespaces)) {
                $chunks[] = array_slice($namespaces, $chunkStart, $k);

                $chunkStart = $k;
            }

            if ($k === count($namespaces) - 1) {
                $chunks[] = array_slice($namespaces, $chunkStart, $k);

                $chunkStart = $k;
            }
        }

        $chunks = array_map(function ($chunk) {
            return [
                'value' => implode('', array_map(function ($token) {
                    return $token[1];
                }, $chunk)),
                'replaces' => [$chunk[0][2], $chunk[array_key_last($chunk)][2]],
            ];
        }, $chunks);

        foreach ($chunks as $chunk) {
            [$from, $to]    = $chunk['replaces'];
            $namespaceStart = -1;
            foreach (array_keys($tokens) as $k) {
                if ($k >= $from && $k <= $to) {
                    if ($namespaceStart === -1) {
                        $namespaceStart = $k;
                    }

                    unset($tokens[$k]);
                }
            }

            $tokens[$namespaceStart] = [T_FULL_NAMESPACE, $chunk['value']];
        }
        ksort($tokens);

        return array_values($tokens);
    }
}
