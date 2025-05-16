<?php

namespace DevTheorem\HandlebarsParser\Test;

use DevTheorem\HandlebarsParser\Ast\Program;
use DevTheorem\HandlebarsParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
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
