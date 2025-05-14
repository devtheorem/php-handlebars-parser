# PHP Handlebars Parser

Parse [Handlebars](https://handlebarsjs.com) templates to a spec-compliant AST with PHP.

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

## Author

Theodore Brown
