<?php

namespace DevTheorem\Handlebars\Ast;

class UndefinedLiteral extends Literal
{
    public function __construct(SourceLocation $loc)
    {
        parent::__construct('UndefinedLiteral', $loc);
    }
}
