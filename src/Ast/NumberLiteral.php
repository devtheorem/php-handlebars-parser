<?php

namespace DevTheorem\Handlebars\Ast;

class NumberLiteral extends Literal
{
    public function __construct(
        public int | float $value,
        public int | float $original,
        SourceLocation $loc,
    ) {
        parent::__construct('NumberLiteral', $loc);
    }
}
