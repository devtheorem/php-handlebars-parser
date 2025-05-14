<?php

namespace DevTheorem\Handlebars\Ast;

readonly class StripFlags
{
    public function __construct(
        public bool $open,
        public bool $close,
    ) {}
}
