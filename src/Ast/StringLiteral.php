<?php

namespace DevTheorem\HandlebarsParser\Ast;

class StringLiteral extends Literal
{
    public function __construct(
        public string $value,
        public string $original,
        SourceLocation $loc,
    ) {
        parent::__construct('StringLiteral', $loc);
    }
}
