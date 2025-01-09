<?php

namespace DevTheorem\Handlebars\Phlexer;

abstract class Phlexer
{
    const INITIAL_STATE = 'INITIAL';

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
     * @return Token[]
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

        $line = substr_count($this->text, "\n", 0, $this->cursor + 1) + 1;
        $subject = substr($this->text, $this->cursor);

        foreach ($this->rules as $rule) {
            if (!$rule->hasStartCondition($this->topState())) {
                continue;
            }

            if (preg_match("/\\A{$rule->pattern}/", $subject, $matches)) {
                $this->yytext = $matches[0];
                $this->cursor += strlen($this->yytext);
                $tokenName = ($rule->handler)();

                if ($tokenName === null) {
                    // skip token - e.g. whitespace or changing state
                    return $this->getNextToken();
                }

                return new Token($tokenName, $this->yytext, $line);
            }
        }

        throw new \Exception('Unexpected token: "' . $subject[0] . '"');
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
