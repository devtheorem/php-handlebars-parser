<?php

namespace DevTheorem\HandlebarsParser;

class ParserFactory
{
    public function create(bool $ignoreStandalone = false): Parser
    {
        return new Parser(new Lexer(), new WhitespaceControl($ignoreStandalone));
    }
}
