# PHP Handlebars Parser

Parse [Handlebars](https://handlebarsjs.com) templates to a spec-compliant AST with PHP.

Implements the same lexical analysis and grammar specification as Handlebars.js, so any template
which can (or cannot) be parsed by Handlebars.js should parse (or error) the same way here.

## Installation

`composer require devtheorem/php-handlebars-parser`

## Usage

```php
use DevTheorem\HandlebarsParser\ParserFactory;

$parser = (new ParserFactory())->create();

$template = "Hello {{name}}!";

$result = $parser->parse($template);
```

If the template contains invalid syntax, an exception will be thrown.
Otherwise, `$result` will contain a `DevTheorem\HandlebarsParser\Ast\Program` instance.

## Whitespace handling

The parser can be created with an optional boolean argument,
to support the `ignoreStandalone` Handlebars compilation option:

```php
$parser = (new ParserFactory())->create(ignoreStandalone: true);
```

## Author

Theodore Brown  
https://theodorejb.me
