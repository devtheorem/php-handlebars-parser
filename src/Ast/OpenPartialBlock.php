<?php

namespace DevTheorem\Handlebars\Ast;

readonly class OpenPartialBlock extends OpenHelper
{
    /**
     * @param Expression[] $params
     */
    public function __construct(
        PathExpression $path,
        array $params,
        ?Hash $hash,
        public StripFlags $strip,
    ) {
        parent::__construct($path, $params, $hash);
    }
}
