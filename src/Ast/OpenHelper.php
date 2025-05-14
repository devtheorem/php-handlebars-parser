<?php

namespace DevTheorem\HandlebarsParser\Ast;

readonly class OpenHelper
{
    /**
     * @param Expression[] $params
     */
    public function __construct(
        public PathExpression $path,
        public array $params,
        public ?Hash $hash,
    ) {}
}
