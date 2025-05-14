<?php

namespace DevTheorem\Handlebars\Ast;

class SubExpression extends Expression
{
    /**
     * @param Expression[] $params
     */
    public function __construct(
        public SubExpression | PathExpression $path,
        public array $params,
        public ?Hash $hash,
        SourceLocation $loc,
    ) {
        parent::__construct('SubExpression', $loc);
    }
}
