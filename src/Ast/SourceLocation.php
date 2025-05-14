<?php

namespace DevTheorem\Handlebars\Ast;

readonly class SourceLocation
{
    public function __construct(
        public string $source,
        public Position $start,
        public Position $end,
    ) {}
}
