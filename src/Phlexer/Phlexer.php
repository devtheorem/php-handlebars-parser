<?php

namespace DevTheorem\HandlebarsParser\Phlexer;

use DevTheorem\HandlebarsParser\ErrorContext;

abstract class Phlexer
{
    public const INITIAL_STATE = 'INITIAL';

    /**
     * @var string[]
     */
    private array $states;
    private string $text;
    private int $cursor;

    /**
     * The current matched value
     */
    protected string $yytext;

    /**
     * @param Rule[] $rules
     */
    public function __construct(protected array $rules) {}

    public function initialize(string $text): void
    {
        $this->states = [self::INITIAL_STATE];
        $this->text = $text;
        $this->cursor = 0;
        $this->yytext = '';
    }

    /**
     * @return list<Token>
     */
    public function tokenize(string $text): array
    {
        $this->initialize($text);
        $tokens = [];

        while ($token = $this->getNextToken()) {
            $tokens[] = $token;
        }

        return $tokens;
    }

    public function hasMoreTokens(): bool
    {
        return $this->cursor < strlen($this->text);
    }

    public function getNextToken(): ?Token
    {
        if (!$this->hasMoreTokens()) {
            return null;
        }

        $subject = substr($this->text, $this->cursor);

        foreach ($this->rules as $rule) {
            if (!$rule->hasStartCondition($this->topState())) {
                continue;
            }

            if (preg_match("/\\A{$rule->pattern}/", $subject, $matches)) {
                [$line, $column] = $this->getPosition();
                $this->yytext = $matches[0];
                $this->cursor += strlen($this->yytext);
                $tokenName = ($rule->handler)();

                if ($tokenName === null) {
                    // skip token - e.g. whitespace or changing state
                    return $this->getNextToken();
                }

                return new Token($tokenName, $this->yytext, $line, $column);
            }
        }

        $line = $this->getPosition()[0];
        $context = ErrorContext::getErrorContext(substr($this->text, 0, $this->cursor), $subject);

        throw new \Exception("Lexical error on line $line. Unrecognized text.\n{$context}");
    }

    /**
     * @return array{string, string}
     */
    public function getPositionContext(int $line, int $column): array
    {
        $lineNum = 1;
        $cursor = 0;
        $textLen = strlen($this->text);

        while ($lineNum < $line && $cursor < $textLen) {
            if ($this->text[$cursor] === "\n") {
                $lineNum++;
            }
            $cursor++;
        }

        $cursor += $column;

        if ($lineNum !== $line || $cursor > $textLen) {
            throw new \Exception("Invalid position $line:$column");
        }

        return [
            substr($this->text, 0, $cursor),
            substr($this->text, $cursor),
        ];
    }

    /**
     * @return array{int, int}
     */
    private function getPosition(): array
    {
        $line = 1;
        $column = -1;

        for ($i = 0; $i < $this->cursor + 1; $i++) {
            if ($this->text[$i] === "\n") {
                $line++;
                $column = -1;
            } else {
                $column++;
            }
        }

        if ($column === -1) {
            $column = 0;
        }

        return [$line, $column];
    }

    protected function pushState(string $state): void
    {
        $this->states[] = $state;
    }

    protected function popState(): void
    {
        array_pop($this->states);
    }

    protected function topState(): string
    {
        $lastKey = array_key_last($this->states);

        if ($lastKey === null) {
            return self::INITIAL_STATE;
        }

        return $this->states[$lastKey];
    }

    protected function rewind(int $length): void
    {
        $this->cursor -= $length;
    }
}
