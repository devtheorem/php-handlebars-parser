<?php

namespace DevTheorem\Handlebars;

use DevTheorem\Handlebars\Phlexer\Phlexer;
use DevTheorem\Handlebars\Phlexer\Rule;

/**
 * Implements the same lexical tokenization from
 * https://github.com/handlebars-lang/handlebars-parser/blob/master/src/handlebars.l
 * (as of 2025-01-07).
 */
final class Lexer extends Phlexer
{
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

                return $this->yytext !== '' ? 'CONTENT' : null;
            }),

            new Rule([], '[^\\x00]+', fn() => 'CONTENT'),

            // marks CONTENT up to the next mustache or escaped mustache
            new Rule(['emu'], '[^\\x00]{2,}?(?={{|\\\\{{|\\\\\\\\{{|\\Z)', function () {
                $this->popState();
                return 'CONTENT';
            }),

            // nested raw block will create stacked 'raw' condition
            new Rule(['raw'], '{{{{(?=[^\\/])', function () {
                $this->pushState('raw');
                return 'CONTENT';
            }),

            new Rule(['raw'], '{{{{\\/' . $CTRL_INVERSE . '(?=[=}\\s\\/.])}}}}', function () {
                $this->popState();

                if ($this->topState() === 'raw') {
                    return 'CONTENT';
                } else {
                    $this->strip(5, 9);
                    return 'END_RAW_BLOCK';
                }
            }),
            new Rule(['raw'], '[^\\x00]+?(?={{{{)', fn() => 'CONTENT'),

            new Rule(['com'], '[\\s\\S]*?--' . $RIGHT_STRIP . '?}}', function () {
                $this->popState();
                return 'COMMENT';
            }),

            new Rule(['mu'], '\\(', fn() => 'OPEN_SEXPR'),
            new Rule(['mu'], '\\)', fn() => 'CLOSE_SEXPR'),

            new Rule(['mu'], '\\[', function () {
                // Assuming yy.syntax.square === 'string'. OPEN_ARRAY option not handled
                $this->rewind(strlen($this->yytext));
                // escaped literal
                $this->pushState('escl');
                return null;
            }),
            new Rule(['mu'], ']', fn() => 'CLOSE_ARRAY'),

            new Rule(['mu'], '{{{{', fn() => 'OPEN_RAW_BLOCK'),
            new Rule(['mu'], '}}}}', function () {
                $this->popState();
                $this->pushState('raw');
                return 'CLOSE_RAW_BLOCK';
            }),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?>', fn() => 'OPEN_PARTIAL'),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?#>', fn() => 'OPEN_PARTIAL_BLOCK'),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?#\\*?', fn() => 'OPEN_BLOCK'),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?\\/', fn() => 'OPEN_ENDBLOCK'),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?\\^\\s*' . $RIGHT_STRIP . '?}}', function () {
                $this->popState();
                return 'INVERSE';
            }),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?\\s*else\\s*' . $RIGHT_STRIP . '?}}', function () {
                $this->popState();
                return 'INVERSE';
            }),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?\\^', fn() => 'OPEN_INVERSE'),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?\\s*else', fn() => 'OPEN_INVERSE_CHAIN'),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?{', fn() => 'OPEN_UNESCAPED'),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?&', fn() => 'OPEN'),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?!--', function () {
                $this->rewind(strlen($this->yytext));
                $this->popState();
                $this->pushState('com');
                return null;
            }),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?![\\s\\S]*?}}', function () {
                $this->popState();
                return 'COMMENT';
            }),
            new Rule(['mu'], '{{' . $LEFT_STRIP . '?\\*?', fn() => 'OPEN'),

            new Rule(['mu'], '=', fn() => 'EQUALS'),
            new Rule(['mu'], '\\.\\.', fn() => 'ID'),
            new Rule(['mu'], '\\.(?=' . $LOOKAHEAD . ')', fn() => 'ID'),
            new Rule(['mu'], '\\.#', fn() => 'PRIVATE_SEP'),
            new Rule(['mu'], '[\\/.]', fn() => 'SEP'),
            new Rule(['mu'], '\\s+', fn() => null), // ignore whitespace
            new Rule(['mu'], '}' . $RIGHT_STRIP . '?}}', function () {
                $this->popState();
                return 'CLOSE_UNESCAPED';
            }),
            new Rule(['mu'], $RIGHT_STRIP . '?}}', function () {
                $this->popState();
                return 'CLOSE';
            }),
            // double-quoted string
            new Rule(['mu'], '"(\\\\["]|[^"])*"', function () {
                $this->strip(1, 2);
                $this->replace('/\\\\"/', '"');
                return 'STRING';
            }),
            // single quoted string
            new Rule(['mu'], "'(\\\\[']|[^'])*'", function () {
                $this->strip(1, 2);
                $this->replace("/\\\\'/", "'");
                return 'STRING';
            }),
            new Rule(['mu'], '@', fn() => 'DATA'),
            new Rule(['mu'], 'true(?=' . $LITERAL_LOOKAHEAD . ')', fn() => 'BOOLEAN'),
            new Rule(['mu'], 'false(?=' . $LITERAL_LOOKAHEAD . ')', fn() => 'BOOLEAN'),
            new Rule(['mu'], 'undefined(?=' . $LITERAL_LOOKAHEAD . ')', fn() => 'UNDEFINED'),
            new Rule(['mu'], 'null(?=' . $LITERAL_LOOKAHEAD . ')', fn() => 'NULL'),
            new Rule(['mu'], '\\-?[0-9]+(?:\\.[0-9]+)?(?=' . $LITERAL_LOOKAHEAD . ')', fn() => 'NUMBER'),
            new Rule(['mu'], 'as\\s+\\|', fn() => 'OPEN_BLOCK_PARAMS'),
            new Rule(['mu'], '\\|', fn() => 'CLOSE_BLOCK_PARAMS'),

            new Rule(['mu'], $ID, fn() => 'ID'),

            new Rule(['escl'], '\\[(\\\\\\]|[^\\]])*\\]', function () {
                $this->replace('/\\\\([\\\\\\]])/', '$1');
                $this->popState();
                return 'ID';
            }),

            new Rule(['mu'], '.', fn() => 'INVALID'),

            new Rule(['INITIAL', 'mu'], '\\Z', fn() => 'EOF'),
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
