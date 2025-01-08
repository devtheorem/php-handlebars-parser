<?php

namespace DevTheorem\Handlebars\Test;

use DevTheorem\Handlebars\Lexer;
use DevTheorem\Handlebars\Phlexer\Token;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    public static function jsonSpecProvider(): array
    {
        $filename = 'vendor/jbboehr/handlebars-spec/spec/tokenizer.json';
        $json = json_decode(file_get_contents($filename), true);
        return array_map(fn(array $d): array => [$d], $json);
    }

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
}
