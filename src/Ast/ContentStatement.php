<?php

namespace DevTheorem\Handlebars\Ast;

class ContentStatement extends Statement
{
    public function __construct(
        public string $value,
        public string $original,
        SourceLocation $loc,
    ) {
        parent::__construct('ContentStatement', $loc);
    }
}
