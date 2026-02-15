<?php

namespace DevTheorem\HandlebarsParser\Test;

use DevTheorem\HandlebarsParser\Ast\Program;
use DevTheorem\HandlebarsParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type ErrorCase array{template: string, expected: string}
 * @phpstan-type SpecToken array{name: string, text: string}
 * @phpstan-type SpecArr array{it: string, number?: string, template: string, expected?: string, exception?: string|true}
 */
class ParserTest extends TestCase
{
    public function testParse(): void
    {
        /*
         * Compare to the following Handlebars.js code:
         *
         * import {parse} from '@handlebars/parser';
         * const template = '{{foo bar}}';
         * const ast = parse(template);
         * process.stdout.write(JSON.stringify(ast, undefined, 4));
         */

        $parser = (new ParserFactory())->create();
        $result = $parser->parse('{{foo bar}}');

        $actual = json_encode($result, JSON_PRETTY_PRINT);
        $this->assertSame(file_get_contents('tests/test1.json'), $actual);
    }

    /**
     * @return list<array{string}>
     */
    public static function successProvider(): array
    {
        return [
            ['{{winner.[.test6]}}'],
            ['{{winner.[#te.st7]}}'],
            ['{{test8}}'],
            ['{{[testD]}}'],
            ['{{te.[est].endK}}'],
            ['{{te.[est]o.endN}}'],
            ['{{te.[e.st].endO}}'],
            ['{{te.[e.s[t].endP}}'],
            ['{{te.[e[s.t].endQ}}'],
            ['{{#with items}}OK!{{/with}}'],
        ];
    }

    #[DataProvider("successProvider")]
    public function testExpectedSuccess(string $template): void
    {
        $parser = (new ParserFactory())->create();

        try {
            $parser->parse($template);
            $this->expectNotToPerformAssertions();
        } catch (\Exception $e) {
            $this->fail("Unexpected exception: {$e->getMessage()}");
        }
    }

    /**
     * @return list<ErrorCase>
     */
    public static function errorProvider(): array
    {
        return [
            [
                'template' => "some\nlong\ncontext\n{{{foo}} additional\nsentence",
                'expected' => "Parse error on line 4:\n...longcontext{{{foo}} additionalsenten"
                    . "\n--------------------^\nExpecting CLOSE_UNESCAPED, got CLOSE",
            ],
            [
                'template' => '{{testerr1}}}',
                'expected' => "Parse error on line 1:\n{{testerr1}}}\n----------^\nExpecting CLOSE, got CLOSE_UNESCAPED",
            ],
            [
                'template' => '{{{testerr2}}',
                'expected' => "Parse error on line 1:\n{{{testerr2}}\n-----------^\nExpecting CLOSE_UNESCAPED, got CLOSE",
            ],
            [
                'template' => '{{{#testerr3}}}',
                'expected' => "Parse error on line 1:\n{{{#testerr3}}}\n---^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{{!testerr4}}}',
                'expected' => "Parse error on line 1:\n{{{!testerr4}}}\n---^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{{^testerr5}}}',
                'expected' => "Parse error on line 1:\n{{{^testerr5}}}\n---^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{{/testerr6}}}',
                'expected' => "Parse error on line 1:\n{{{/testerr6}}}\n---^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got SEP",
            ],
            [
                'template' => '{{win[ner.test1}}',
                'expected' => "Parse error on line 1:\n{{win[ner.test1}}\n--^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{win]ner.test2}}',
                'expected' => "Parse error on line 1:\n{{win]ner.test2}}\n--^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{wi[n]ner.test3}}',
                'expected' => "Parse error on line 1:\n{{wi[n]ner.test3}}\n--^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{winner].[test4]}}',
                'expected' => "Parse error on line 1:\n{{winner].[test4]}}\n--^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{winner[.test5]}}',
                'expected' => "Parse error on line 1:\n{{winner[.test5]}}\n--^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{test9]}}',
                'expected' => "Parse error on line 1:\n{{test9]}}\n--^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{testA[}}',
                'expected' => "Parse error on line 1:\n{{testA[}}\n--^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{[testB}}',
                'expected' => "Parse error on line 1:\n{{[testB}}\n--^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{]testC}}',
                'expected' => "Parse error on line 1:\n{{]testC}}\n--^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{te]stE}}',
                'expected' => "Parse error on line 1:\n{{te]stE}}\n--^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{tee[stF}}',
                'expected' => "Parse error on line 1:\n{{tee[stF}}\n--^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{te.e[stG}}',
                'expected' => "Parse error on line 1:\n{{te.e[stG}}\n-----^\nExpecting ID, got INVALID",
            ],
            [
                'template' => '{{te.e]stH}}',
                'expected' => "Parse error on line 1:\n{{te.e]stH}}\n-----^\nExpecting ID, got INVALID",
            ],
            [
                'template' => '{{te.e[st.endI}}',
                'expected' => "Parse error on line 1:\n{{te.e[st.endI}}\n-----^\nExpecting ID, got INVALID",
            ],
            [
                'template' => '{{te.e]st.endJ}}',
                'expected' => "Parse error on line 1:\n{{te.e]st.endJ}}\n-----^\nExpecting ID, got INVALID",
            ],
            [
                'template' => '{{te.t[est].endL}}',
                'expected' => "Parse error on line 1:\n{{te.t[est].endL}}\n-----^\nExpecting ID, got INVALID",
            ],
            [
                'template' => '{{te.t[est]o.endM}}',
                'expected' => "Parse error on line 1:\n{{te.t[est]o.endM}}\n-----^\nExpecting ID, got INVALID",
            ],
            [
                'template' => '<ul>{{#each item}}<li>{{name}}</li>',
                'expected' => "Parse error on line 1:\n...m}}<li>{{name}}</li>\n-----------------------^\nExpecting OPEN_ENDBLOCK, got EOF",
            ],
            [
                'template' => 'issue63: {{test_join}} Test! {{this}} {{/test_join}}',
                'expected' => "Parse error on line 1:\n...in}} Test! {{this}} {{/test_join}}\n-----------------------^\nExpecting EOF, got OPEN_ENDBLOCK",
            ],
            [
                'template' => '{{#if a}}TEST{{/with}}',
                'expected' => "if doesn't match with - 1:3",
            ],
            [
                'template' => '{{#foo}}error{{/bar}}',
                'expected' => "foo doesn't match bar - 1:3",
            ],
            [
                'template' => '{{{{foo}}}} {{ {{{{/bar}}}}',
                'expected' => "foo doesn't match bar - 1:4",
            ],
            [
                'template' => '{{a=b}}',
                'expected' => "Parse error on line 1:\n{{a=b}}\n---^\nExpecting CLOSE, got EQUALS",
            ],
            [
                'template' => '{{#with a}OK!{{/with}}',
                'expected' => "Parse error on line 1:\n{{#with a}OK!{{/with}}\n---------^\nExpecting CLOSE, got INVALID",
            ],
            [
                'template' => '{{#each a}OK!{{/each}}',
                'expected' => "Parse error on line 1:\n{{#each a}OK!{{/each}}\n---------^\nExpecting CLOSE, got INVALID",
            ],
            [
                'template' => '{{1 + 2}}',
                'expected' => "Parse error on line 1:\n{{1 + 2}}\n----^\nExpecting CLOSE, got INVALID",
            ],
            [
                'template' => '{{{{#foo}}}',
                'expected' => "Parse error on line 1:\n{{{{#foo}}}\n----^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
            [
                'template' => '{{foo (foo (foo 1 2) 3))}}',
                'expected' => "Parse error on line 1:\n...oo (foo (foo 1 2) 3))}}\n-----------------------^\nExpecting CLOSE, got CLOSE_SEXPR",
            ],
            [
                'template' => '{{{{foo}}}} {{ {{{{#foo}}}}',
                'expected' => "Lexical error on line 1. Unrecognized text.\n{{{{foo}}}} {{ {{{{#foo}}}}\n-------------------^",
            ],
            [
                'template' => '{{else}}',
                'expected' => "Parse error on line 1:\n{{else}}\n^\nExpecting EOF, got INVERSE",
            ],
            [
                'template' => '{{#>foo}}bar',
                'expected' => "Parse error on line 1:\n{{#>foo}}bar\n------------^\nExpecting OPEN_ENDBLOCK, got EOF",
            ],
            [
                'template' => '{{ #2 }}',
                'expected' => "Parse error on line 1:\n{{ #2 }}\n---^\nExpecting BOOLEAN, DATA, ID, NULL, NUMBER, OPEN_SEXPR, STRING, UNDEFINED, got INVALID",
            ],
        ];
    }

    #[DataProvider("errorProvider")]
    public function testErrors(string $template, string $expected): void
    {
        $parser = (new ParserFactory())->create();

        try {
            $parser->parse($template);
            $this->fail("Expected to throw exception: {$expected}");
        } catch (\Exception $e) {
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * @return \Generator<array{0: SpecArr}>
     */
    public static function jsonSpecProvider(): \Generator
    {
        $files = [
            'basic',
            'blocks',
            'builtins',
            'data',
            'helpers',
            'parser',
            'partials',
            'regressions',
            'strict',
            'string-params',
            'subexpressions',
            'track-ids',
            'whitespace-control',
        ];

        foreach ($files as $file) {
            $filename = "vendor/jbboehr/handlebars-spec/spec/{$file}.json";
            $contents = file_get_contents($filename);

            if ($contents === false) {
                throw new \Exception("Failed to read file {$filename}");
            }

            /** @var list<SpecArr> $json */
            $json = json_decode($contents, true);

            foreach ($json as $spec) {
                yield [$spec];
            }
        }
    }

    /**
     * @param SpecArr $spec
     */
    #[DataProvider("jsonSpecProvider")]
    public function testSpecs(array $spec): void
    {
        $parser = (new ParserFactory())->create();

        try {
            $result = $parser->parse($spec['template']);
            $this->assertInstanceOf(Program::class, $result);
        } catch (\Exception $e) {
            if (isset($spec['exception'])) {
                if (is_string($spec['exception'])) {
                    if (str_starts_with($spec['exception'], '/')) {
                        $this->assertMatchesRegularExpression($spec['exception'], $e->getMessage());
                    } else {
                        $this->assertStringContainsString($e->getMessage(), $spec['exception']);
                    }
                } else {
                    $this->assertNotEmpty($e->getMessage());
                }
            } else {
                $this->fail("Unexpected exception: {$e->getMessage()}");
            }
        }
    }
}
