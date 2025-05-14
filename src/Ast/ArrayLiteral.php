<?php

namespace DevTheorem\Handlebars\Ast;

class ArrayLiteral extends Literal
{
    /**
     * @param Expression[] $items
     */
    public function __construct(
        public array $items,
        SourceLocation $loc,
    ) {
        parent::__construct('ArrayLiteral', $loc);
    }
}
