<?php

namespace DevTheorem\HandlebarsParser\Ast;

class HashLiteral extends Expression
{
    /**
     * @param HashPair[] $pairs
     */
    public function __construct(
        public array $pairs,
        SourceLocation $loc,
    ) {
        parent::__construct('HashLiteral', $loc);
    }
}
