<?php

namespace DevTheorem\Handlebars\Ast;

class HashPair extends Node
{
    public function __construct(
        public string $key,
        public Expression $value,
        SourceLocation $loc,
    ) {
        parent::__construct('HashPair', $loc);
    }
}
