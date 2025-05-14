<?php

namespace DevTheorem\HandlebarsParser;

use DevTheorem\HandlebarsParser\Phlexer\Phlexer;
use DevTheorem\HandlebarsParser\Phlexer\Rule;

/**
 * Implements the same lexical tokenization from
 * https://github.com/handlebars-lang/handlebars-parser/blob/master/src/handlebars.l
 * (as of 2025-01-07).
 */
final class Lexer extends Phlexer
{
    const T_BOOLEAN = 'BOOLEAN';
    const T_CLOSE = 'CLOSE';
    const T_CLOSE_ARRAY = 'CLOSE_ARRAY';
    const T_CLOSE_BLOCK_PARAMS = 'CLOSE_BLOCK_PARAMS';
    const T_CLOSE_RAW_BLOCK = 'CLOSE_RAW_BLOCK';
    const T_CLOSE_SEXPR = 'CLOSE_SEXPR';
    const T_CLOSE_UNESCAPED = 'CLOSE_UNESCAPED';
    const T_COMMENT = 'COMMENT';
    const T_CONTENT = 'CONTENT';
    const T_DATA = 'DATA';
    const T_END_RAW_BLOCK = 'END_RAW_BLOCK';
    const T_EOF = 'EOF';
    const T_EQUALS = 'EQUALS';
    const T_ID = 'ID';
    const T_INVALID = 'INVALID';
    const T_INVERSE = 'INVERSE';
    const T_NULL = 'NULL';
    const T_NUMBER = 'NUMBER';
    const T_OPEN = 'OPEN';
    const T_OPEN_BLOCK = 'OPEN_BLOCK';
    const T_OPEN_BLOCK_PARAMS = 'OPEN_BLOCK_PARAMS';
    const T_OPEN_ENDBLOCK = 'OPEN_ENDBLOCK';
    const T_OPEN_INVERSE = 'OPEN_INVERSE';
    const T_OPEN_INVERSE_CHAIN = 'OPEN_INVERSE_CHAIN';
    const T_OPEN_PARTIAL = 'OPEN_PARTIAL';
    const T_OPEN_PARTIAL_BLOCK = 'OPEN_PARTIAL_BLOCK';
    const T_OPEN_RAW_BLOCK = 'OPEN_RAW_BLOCK';
    const T_OPEN_SEXPR = 'OPEN_SEXPR';
    const T_OPEN_UNESCAPED = 'OPEN_UNESCAPED';
    const T_PRIVATE_SEP = 'PRIVATE_SEP';
    const T_SEP = 'SEP';
    const T_STRING = 'STRING';
    const T_UNDEFINED = 'UNDEFINED';

    public function __construct()
    {
        $LEFT_STRIP = $RIGHT_STRIP = '~';
        $LOOKAHEAD = '[=~}\\s\\/.)\\]|]';
        $LITERAL_LOOKAHEAD = '[~}\\s)\\]]';

        /*
         * ID is the inverse of control characters.
         * Control characters ranges:
         *  [\s]          Whitespace
         *  [!"#%-,\./]   !, ", #, %, &, ', (, ), *, +, ,, ., /,  Exceptions in range: $, -
         *  [;->@]        ;, <, =, >, @,                          Exceptions in range: :, ?
         *  [\[-\^`]      [, \, ], ^, `,                          Exceptions in range: _
         *  [\{-~]        {, |, }, ~
         */
        $CTRL_INVERSE = '[^\\s!"#%-,\\.\\/;->@\\[-\\^`\\{-~]+';
        $ID = $CTRL_INVERSE . '(?=' . $LOOKAHEAD . ')';

        parent::__construct([
            new Rule([], '[^\\x00]*?(?={{)', function () {
                if (str_ends_with($this->yytext, "\\\\")) {
                    $this->strip(0, 1);
                    $this->pushState('mu');
                } elseif (str_ends_with($this->yytext, "\\")) {
                    $this->strip(0, 1);
                    $this->pushState('emu');
                } else {
                    $this->pushState('mu');
                }

                return $this->yytext !== '' ? self::T_CONTENT : null;
            }),

            new Rule([], '[^\\x00]+', fn() => self::T_CONTENT),

            // marks CONTENT up to the next mustache or escaped mustache
            new Rule(['emu'], '[^\\x00]{2,}?(?={{|\\\\{{|\\\\\\\\{{|\\Z)', function () {
                $this->popState();
                return self::T_CONTENT;
            }),

            // nested raw block will create stacked 'raw' condition
            new Rule(['raw'], '{{{{(?=[^\\/])', function () {
                $this->pushState('raw');
                return self::T_CONTENT;
            }),

            new Rule(['raw'], '{{{{\\/' . $CTRL_INVERSE . '(?=[=}\\s\\/.])}}}}', function () {
                $this->popState();

                if ($this->topState() === 'raw') {
                    return self::T_CONTENT;
                } else {
                    $this->strip(5, 9);
                    return self::T_END_RAW_BLOCK;
                }
            }),
            new Rule(['raw'], '[^\\x00]+?(?={{{{)', fn() => self::T_CONTENT),

            new Rule(['com'], '[\\s\\S]*?--' . $RIGHT_STRIP . '?}}', function () {
                $this->popState();
                return self::T_COMMENT;
            }),

            new Rule(['mu'], '\\(', fn() => self::T_OPEN_SEXPR),
            new Rule(['mu'], '\\)', fn() => self::T_CLOSE_SEXPR),

            new Rule(['mu'], '\\[', function () {
                // Assuming yy.syntax.square === 'string'. OPEN_ARRAY option not handled
                $this->rewind(strlen($this->yytext));
                // escaped literal
                $this->pushState('escl');
                return null;
            }),
            new Rule(['mu'], ']', fn() => self::T_CLOSE_ARRAY),

            new Rule(['mu'], '{{{{', fn() => self::T_OPEN_RAW_BLOCK),
            new Rule(['mu'], '}}}}', function () {
                $this->popState();
                $this->pushState('raw');
                return self::T_CLOSE_RAW_BLOCK;
            }),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?>', fn() => self::T_OPEN_PARTIAL),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?#>', fn() => self::T_OPEN_PARTIAL_BLOCK),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?#\\*?', fn() => self::T_OPEN_BLOCK),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?\\/', fn() => self::T_OPEN_ENDBLOCK),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?\\^\\s*' . $RIGHT_STRIP . '?}}', function () {
                $this->popState();
                return self::T_INVERSE;
            }),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?\\s*else\\s*' . $RIGHT_STRIP . '?}}', function () {
                $this->popState();
                return self::T_INVERSE;
            }),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?\\^', fn() => self::T_OPEN_INVERSE),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?\\s*else', fn() => self::T_OPEN_INVERSE_CHAIN),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?{', fn() => self::T_OPEN_UNESCAPED),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?&', fn() => self::T_OPEN),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?!--', function () {
                $this->rewind(strlen($this->yytext));
                $this->popState();
                $this->pushState('com');
                return null;
            }),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?![\\s\\S]*?}}', function () {
                $this->popState();
                return self::T_COMMENT;
            }),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?\\*?', fn() => self::T_OPEN),

            new Rule(['mu'], '=', fn() => self::T_EQUALS),
            new Rule(['mu'], '\\.\\.', fn() => self::T_ID),
            new Rule(['mu'], '\\.(?=' . $LOOKAHEAD . ')', fn() => self::T_ID),
            new Rule(['mu'], '\\.#', fn() => self::T_PRIVATE_SEP),
            new Rule(['mu'], '[\\/.]', fn() => self::T_SEP),
            new Rule(['mu'], '\\s+', fn() => null), // ignore whitespace
            new Rule(['mu'], '}' . $RIGHT_STRIP . '?}}', function () {
                $this->popState();
                return self::T_CLOSE_UNESCAPED;
            }),
            new Rule(['mu'], $RIGHT_STRIP . '?}}', function () {
                $this->popState();
                return self::T_CLOSE;
            }),
            // double-quoted string
            new Rule(['mu'], '"(\\\\["]|[^"])*"', function () {
                $this->strip(1, 2);
                $this->replace('/\\\\"/', '"');
                return self::T_STRING;
            }),
            // single quoted string
            new Rule(['mu'], "'(\\\\[']|[^'])*'", function () {
                $this->strip(1, 2);
                $this->replace("/\\\\'/", "'");
                return self::T_STRING;
            }),
            new Rule(['mu'], '@', fn() => self::T_DATA),
            new Rule(['mu'], 'true(?=' . $LITERAL_LOOKAHEAD . ')', fn() => self::T_BOOLEAN),
            new Rule(['mu'], 'false(?=' . $LITERAL_LOOKAHEAD . ')', fn() => self::T_BOOLEAN),
            new Rule(['mu'], 'undefined(?=' . $LITERAL_LOOKAHEAD . ')', fn() => self::T_UNDEFINED),
            new Rule(['mu'], 'null(?=' . $LITERAL_LOOKAHEAD . ')', fn() => self::T_NULL),
            new Rule(['mu'], '\\-?[0-9]+(?:\\.[0-9]+)?(?=' . $LITERAL_LOOKAHEAD . ')', fn() => self::T_NUMBER),
            new Rule(['mu'], 'as\\s+\\|', fn() => self::T_OPEN_BLOCK_PARAMS),
            new Rule(['mu'], '\\|', fn() => self::T_CLOSE_BLOCK_PARAMS),

            new Rule(['mu'], $ID, fn() => self::T_ID),

            new Rule(['escl'], '\\[(\\\\\\]|[^\\]])*\\]', function () {
                $this->replace('/\\\\([\\\\\\]])/', '$1');
                $this->popState();
                return self::T_ID;
            }),

            new Rule(['mu'], '.', fn() => self::T_INVALID),

            new Rule(['INITIAL', 'mu'], '\\Z', fn() => self::T_EOF),
        ]);
    }

    private function strip(int $start, int $end): void
    {
        $this->yytext = substr($this->yytext, $start, strlen($this->yytext) - $end);
    }

    private function replace(string $pattern, string $replacement): void
    {
        $result = preg_replace($pattern, $replacement, $this->yytext);

        if ($result === null) {
            throw new \Exception('Failed to replace string: ' . preg_last_error_msg());
        }

        $this->yytext = $result;
    }
}
