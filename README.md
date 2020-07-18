# Extended Tokens

```
composer require delight/extended-tokens
```

This package is meant to facilitate PHP code highlighting by changing T_STRING tokens to a more accurate representation, like T_CLASS_NAME.

PHP ships by default with a Parser for PHP via the method `token_get_all`

This function returns an array of tokens that looks like this : 

```php
[
    T_TOKENTYPE,
    "value",
    42 // offset
];
```
> Note: This library returns tokens without offset.

Sometimes for tokens like `{`, `;`, it just returns the literal string, not an array. 

This library change this, everything is an array, even those useless string.

## Usage

```php
use Delight\ExtendedTokens\ExtendedTokens;

$parser = new ExtendedTokens();
$tokens = $parser->parse('code');
```

### T_CLASS_NAME
```php
class A {}
```
* `A` will be a T_CLASS_NAME

```php
new A;
```
* `A` will be a T_CLASS_NAME

### T_FUNCTION_NAME
```php
function hello() {}
```
* `hello` will be a T_FUNCTION_NAME

### T_CONST_NAME
```php
const E = 'F';
```
* `E` will be a T_CONST_NAME

### T_EQUAL
```php
$a = 1;
```
* `=` will be a T_EQUAL

### T_CONCAT
```php
$c = 'a' . 'b';

```
* `.` will be a T_CONCAT

### T_VARIABLE after T_OBJECT_OPERATOR
```php
$a->b;
```
* `b` will be a T_VARIABLE instead of a T_STRING

### T_VARIABLE_TYPE
```php
function world(string $name): World {}
```
* `string` will be a T_VARIABLE_TYPE
* `World` will be a T_CLASS_NAME

### T_TRUE and T_FALSE
```php
true && false;
```
* `true` will be a T_TRUE
* `false` will be a T_FALSE

### T_REF
```php
function (int &$index) {}
```
* `&` will be a T_REF
