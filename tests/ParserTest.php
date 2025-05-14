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
    /**
     * @return list<array{0: SpecArr}>
     */
    public static function jsonSpecProvider(): array
    {
        $filename = 'vendor/jbboehr/handlebars-spec/spec/parser.json';
        $contents = file_get_contents($filename);

        if ($contents === false) {
            throw new \Exception("Failed to read file {$filename}");
        }

        /** @var list<SpecArr> $json */
        $json = json_decode($contents, true);
        return array_map(fn(array $d): array => [$d], $json);
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
                    $this->assertMatchesRegularExpression($spec['exception'], $e->getMessage());
                } else {
                    $this->assertNotEmpty($e->getMessage());
                }

            } else {
                $this->fail("Unexpected exception: {$e->getMessage()}");
            }
        }
    }
}
