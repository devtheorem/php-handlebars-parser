<?php

namespace DevTheorem\Handlebars\Ast;

class BooleanLiteral extends Literal
{
    public function __construct(
        public bool $value,
        public bool $original,
        SourceLocation $loc,
    ) {
        parent::__construct('BooleanLiteral', $loc);
    }
}
