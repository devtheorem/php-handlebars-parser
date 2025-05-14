<?php

namespace DevTheorem\HandlebarsParser\Ast;

class NullLiteral extends Literal
{
    public function __construct(SourceLocation $loc)
    {
        parent::__construct('NullLiteral', $loc);
    }
}
