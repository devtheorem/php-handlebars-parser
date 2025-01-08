<?php

namespace DevTheorem\Handlebars\Test;

use DevTheorem\Handlebars\Lexer;
use DevTheorem\Handlebars\Phlexer\Token;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type SpecToken array{name: string, text: string}
 * @phpstan-type SpecArr array{it: string, template: string, expected: list<SpecToken>}
 */
class LexerTest extends TestCase
{
    /**
     * @return list<array{0: SpecArr}>
     */
    public static function jsonSpecProvider(): array
    {
        $filename = 'vendor/jbboehr/handlebars-spec/spec/tokenizer.json';
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
        // fix invalid expectations
        if ($spec['it'] === 'does not time out in a mustache with a single } followed by EOF') {
            $spec['expected'][] = ['name' => 'INVALID', 'text' => '}'];
        } elseif ($spec['it'] === 'does not time out in a mustache when invalid ID characters are used') {
            $spec['expected'][] = ['name' => 'INVALID', 'text' => '&'];
            $spec['expected'][] = ['name' => 'CLOSE', 'text' => '}}'];
        }

        $lexer = new Lexer();
        $toJson = fn(Token $t) => ['name' => $t->name, 'text' => $t->text];
        $actual = array_map($toJson, $lexer->tokenize($spec['template']));
        $this->assertSame($spec['expected'], $actual);
    }

    public function testLineNumbers(): void
    {
        $template = <<<_tpl
            This
            is a {{template}}
            with multiple
            {{lines}}
            _tpl;
        $expected = [
            new Token('CONTENT', "This\nis a ", 1),
            new Token('OPEN', '{{', 2),
            new Token('ID', 'template', 2),
            new Token('CLOSE', '}}', 2),
            new Token('CONTENT', "\nwith multiple\n", 3),
            new Token('OPEN', '{{', 4),
            new Token('ID', 'lines', 4),
            new Token('CLOSE', '}}', 4),
        ];
        $this->assertEquals($expected, (new Lexer())->tokenize($template));
    }
}
