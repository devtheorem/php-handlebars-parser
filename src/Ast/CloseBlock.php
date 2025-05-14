<?php

namespace DevTheorem\Handlebars\Ast;

readonly class CloseBlock
{
    public function __construct(
        public PathExpression $path,
        public StripFlags $strip,
    ) {}
}
