<?php

namespace DevTheorem\HandlebarsParser\Ast;

readonly class CloseBlock
{
    public function __construct(
        public PathExpression $path,
        public StripFlags $strip,
    ) {}
}
