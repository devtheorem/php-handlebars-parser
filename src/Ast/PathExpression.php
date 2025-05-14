<?php

namespace DevTheorem\HandlebarsParser\Ast;

class PathExpression extends Expression
{
    /**
     * @param (string | SubExpression)[] $parts
     * @param string[] $tail
     */
    public function __construct(
        public bool $this_,
        public bool $data,
        public int $depth,
        public SubExpression|string $head,
        public array $tail,
        public array $parts,
        public string $original,
        SourceLocation $loc,
    ) {
        parent::__construct('PathExpression', $loc);
    }
}
